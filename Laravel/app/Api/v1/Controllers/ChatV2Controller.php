<?php
namespace App\Api\v1\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redis;
use Dingo\Api\Routing\Helpers;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Davibennun\LaravelPushNotification\Facades\PushNotification;
use Validator;
use Config;
use App\Name_cards;
use App\Users;
use App\Sessions;
use App\Chats;
use App\Api\v1\Utilities\ErrorCodeUtility;
use DateTime;

class ChatV2Controller extends Controller 
{
    use Helpers;
    
    public function __construct(Request $request) 
    {
        $this->request = $request;
    }

    public function send() 
    {
        $this->sendValidation($this->request);
        $sender_id = $this->request->self_user_id;
        $receiver_id = $this->request->receiver_id;
        $user_a_id = min($sender_id, $receiver_id);
        $user_b_id = max($sender_id, $receiver_id);
        $chat = Chats::where('user_a_id', $user_a_id)->where('user_b_id', $user_b_id)->first();
        if(is_null($chat))
        {
            $chat = new Chats;
            $chat->user_a_id = $user_a_id;
            $chat->user_b_id = $user_b_id;
        }
        if(($sender_id == $chat->user_a_id && $chat->user_a_unread_count > 0) || ($sender_id == $chat->user_b_id && $chat->user_b_unread_count > 0))
        {
            return response()->json([
                'message' => 'Please mark unread messages before sending new messages!',
                'error_code' => ErrorCodeUtility::UNMARKED_MESSAGE,
                'status_code' => '400'
            ], 400);
        }
        if($sender_id == $receiver_id)
        {
            return response()->json([
                'message' => 'You can not send messages to yourself!',
                'error_code' => ErrorCodeUtility::SEND_TO_SELF,
                'status_code' => '400'
            ], 400);
        }
        $chat->last_message = $this->request->message;
        $chat->last_message_type = $this->request->type;
        $chat->last_message_sender_id = $sender_id;
        $chat->updateTimestamp();
        if($receiver_id == $user_a_id)
        {
            $chat->user_a_unread_count++;
        }
        else
        {
            $chat->user_b_unread_count++;
        }
        $chat->save();

        $chat_message = $this->request->toArray();
        $chat_message['message_sender_id'] = $sender_id;
        $chat_message['message_timestamp'] = $chat->updated_at->format('Y-m-d H:i:s');
        Redis::RPUSH($receiver_id.':'.$chat->id,  json_encode($chat_message));

        if(Config::get('app.pushback')==true) {
            $sessions = Sessions::where('user_id', $receiver_id)->get();
            $sender = Users::find($sender_id);
            foreach($sessions as $key=>$value) {
                if ($value->is_mobile && !empty($value->device_id)){
                    $message = PushNotification::Message(
                        ($chat->last_message_type=='text')?($sender->user_name.': '.$this->request->message) : ($sender->user_name.' sent you a '.$chat->last_message_type),
                        array(
                        'badge' => SyncController::getAPNSCount($receiver_id),
                        'custom' => array('custom data' => array(
                            'type' => 'chat_new_message',
                            'chat_id' => $chat->id,
                            'last_message' => $chat->last_message,
                            'last_message_sender_id' => $chat->last_message_sender_id,
                            'last_message_timestamp' => $chat->last_message_timestamp,
                            'last_message_type' => $chat->last_message_type,
                            //the unread count of the sender should be 0, so the add could reduce the if statment of determine who is the receiver
                            'unread_count' => $chat->user_a_unread_count + $chat->user_b_unread_count
                        ))
                    ));

                    try{
                        $collection = PushNotification::app('appNameIOS')
                        ->to($value->device_id)
                        ->send($message);
                    }catch(\Exception $e){
                        
                    }
                    
                }
            }
        }
        return $this->response->created(null, ['chat_id' => $chat->id]);
    }

    public function getUnread() 
    {
        $unreads = Chats::where(function ($query) {
                        $query->where('user_a_id', $this->request->self_user_id)->where('user_a_unread_count', '>', 0);
                    })->orWhere(function ($query) {
                        $query->where('user_b_id', $this->request->self_user_id)->where('user_b_unread_count', '>', 0);
                    })
                    ->orderBy('last_message_timestamp', 'desc')
                    ->get();
        $messages = array();
        foreach($unreads as $unread)
        {
            $unread_count = ($this->request->self_user_id == $unread->user_a_id) ? $unread->user_a_unread_count:$unread->user_b_unread_count;
            $time = date("Y-m-d H:i:s");
            $last_message_sender = Users::find($unread->last_message_sender_id);
            if(is_null($last_message_sender))
            {
                return response()->json([
                    'message' => 'last message sender not found',
                    'error_code' => ErrorCodeUtility::LAST_MESSAGE_SENDER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
            }
            $messages[] = ['chat_id' => $unread->id, 'last_message' => $unread->last_message, 
                           'last_message_sender_id' => $unread->last_message_sender_id, 
                           'last_message_sender_name' => $last_message_sender->user_name, 
                           'last_message_timestamp' => $unread->last_message_timestamp, 
                           'last_message_type' => $unread->last_message_type, 'unread_count' => $unread_count, 
                           'server_sent_timestamp' => $time];
        }
        return $this->response->array($messages);
    }

    public function delete($chat_id) 
    {
        if(!is_numeric($chat_id)) 
        {
            return response()->json([
                    'message' => 'chat_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        $chat = Chats::find($chat_id);
        if(is_null($chat))
        {
            return response()->json([
                    'message' => 'chat not found',
                    'error_code' => ErrorCodeUtility::CHAT_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        if($this->request->self_user_id != $chat->user_a_id && $this->request->self_user_id != $chat->user_b_id)
        {
            throw new UnauthorizedHttpException(null, 'Bad request, you have no right to delete this chat');
        }
        
        Redis::DEL($chat->user_b_id.':'.$chat->id);
        Redis::DEL($chat->user_a_id.':'.$chat->id);
        $chat->delete();
        return $this->response->noContent();
    }

    public function getChatIdFromUserId($user_a_id, $user_b_id)
    {
        if(!is_numeric($user_a_id) || !is_numeric($user_b_id) )
        {
            return response()->json([
                    'message' => 'user_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        if($this->request->self_user_id != $user_a_id && $this->request->self_user_id != $user_b_id) {
            return response()->json([
                    'message' => 'user not in this chat',
                    'error_code' => ErrorCodeUtility::USER_NOT_IN_CHAT,
                    'status_code' => '403'
                ], 403);
        }
        $first = min($user_a_id,$user_b_id);
        $second = max($user_a_id,$user_b_id);
        $chat = Chats::where('user_a_id', $first)->where('user_b_id', $second)->first();
        if(is_null($chat))
        {
            return response()->json([
                    'message' => 'chat not found',
                    'error_code' => ErrorCodeUtility::CHAT_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        return $this->response->array(array("chat_id" => $chat->id));
    }

    public function getMessageByUserId($user_a_id, $user_b_id) {
        if(!is_numeric($user_a_id) || !is_numeric($user_b_id) )
        {
            return response()->json([
                    'message' => 'user_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        if($this->request->self_user_id != $user_a_id && $this->request->self_user_id != $user_b_id) {
            return response()->json([
                    'message' => 'user not in this chat',
                    'error_code' => ErrorCodeUtility::USER_NOT_IN_CHAT,
                    'status_code' => '403'
                ], 403);
        }
        $first = min($user_a_id,$user_b_id);
        $second = max($user_a_id,$user_b_id);
        $chat = Chats::where('user_a_id', $first)->where('user_b_id', $second)->first();
        if(is_null($chat))
        {
            return response()->json([
                    'message' => 'chat not found',
                    'error_code' => ErrorCodeUtility::CHAT_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }

        
        $messages = array();           
        $message_count = 0;
        $message = '';
        while(!is_null($message = Redis::LPOP($this->request->self_user_id.':'.$chat->id))){
            $message_obj = json_decode($message, true);
            $message_obj['chat_id'] =  $chat->id;
            $messages[] = $message_obj;
            $message_count++;
            if($message_count == 50){
                break;
            }
        }
        $unread_count = 0;
        if($this->request->self_user_id == $chat->user_a_id){
            $chat->user_a_unread_count -=$message_count;
            $unread_count = $chat->user_a_unread_count;
        }
        else{
            $chat->user_b_unread_count -=$message_count;
            $unread_count = $chat->user_b_unread_count;
        }
        $chat->save();
        return response()->json([
                    'unread_count' => $unread_count,
                    'messages' => $messages
                ], 200);
    }

    public function getMessageByChatId($chat_id) {
        if(!is_numeric($chat_id)) 
        {
            return response()->json([
                    'message' => 'chat_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        $chat = Chats::find($chat_id);
        if(is_null($chat))
        {
            return response()->json([
                    'message' => 'chat not found',
                    'error_code' => ErrorCodeUtility::CHAT_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        if($this->request->self_user_id != $chat->user_a_id && $this->request->self_user_id != $chat->user_b_id) {
            return response()->json([
                    'message' => 'user not in this chat',
                    'error_code' => ErrorCodeUtility::USER_NOT_IN_CHAT,
                    'status_code' => '403'
                ], 403);
        }

        $messages = array();           
        $message_count = 0;
        $message = '';
        while(!is_null($message = Redis::LPOP($this->request->self_user_id.':'.$chat_id))){
            $message_obj = json_decode($message, true);
            $message_obj['chat_id'] =  $chat_id;
            $messages[] = $message_obj;
            $message_count++;
            if($message_count == 50){
                break;
            }
        }
        $unread_count = 0;
        if($this->request->self_user_id == $chat->user_a_id){
            $chat->user_a_unread_count -=$message_count;
            $unread_count = $chat->user_a_unread_count;
        }
        else{
            $chat->user_b_unread_count -=$message_count;
            $unread_count = $chat->user_b_unread_count;
        }
        $chat->save();
        return response()->json([
                    'unread_count' => $unread_count,
                    'messages' => $messages
                ], 200);
    }

    private function sendValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'type' => 'required|in:text,image,sticker,location,audio,customize',
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not send message.',$validator->errors());
        }
    }
}

