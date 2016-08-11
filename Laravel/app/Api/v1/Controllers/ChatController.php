<?php
namespace App\Api\v1\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Davibennun\LaravelPushNotification\Facades\PushNotification;
use Validator;
use Config;
use App\Users;
use App\Sessions;
use App\Chats;

class ChatController extends Controller 
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
            return $this->response->errorBadRequest('Please mark unread messages before sending new messages!');
        }
        if($sender_id == $receiver_id)
        {
            return $this->response->errorBadRequest('You can not send messages to yourself!');
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

        if(Config::get('app.pushback')==true) {
            $sessions = Sessions::where('user_id', $receiver_id)->get();
            foreach($sessions as $key=>$value) {
                if ($value->is_mobile && !empty($value->device_id)){
                    $message = PushNotification::Message('New message',array(
                        'badge' => 1,
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

                    $collection = PushNotification::app('appNameIOS')
                        ->to($value->device_id)
                        ->send($message);
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
                    ->get();

        $messages = array();
        foreach($unreads as $unread)
        {
            $unread_count = ($this->request->self_user_id == $unread->user_a_id) ? $unread->user_a_unread_count:$unread->user_b_unread_count;
            $messages[] = ['chat_id' => $unread->id, 'last_message' => $unread->last_message, 'last_message_sender_id' => $unread->last_message_sender_id, 'last_message_timestamp' => $unread->last_message_timestamp->format('Y-m-d H:i:s'), 'last_message_type' => $unread->last_message_type, 'unread_count' => $unread_count];
        }
        return $this->response->array($messages);
    }

    public function markRead() 
    {
        $validator = Validator::make($this->request->all(), [
            'chat_id' => 'required|integer|exists:chats,id',
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not mark unread message.',$validator->errors());
        }

        $chat = Chats::find($this->request->chat_id);
        if(is_null($chat))
        {
            return $this->response->errorNotFound();
        }
        if($this->request->self_user_id == $chat->user_a_id)
        {
            $chat->user_a_unread_count = 0;
            $chat->save();
        }
        else if($this->request->self_user_id == $chat->user_b_id)
        {
            $chat->user_b_unread_count = 0;
            $chat->save();
        }
        else
        {
            return $this->response->errorNotFound();
        }
        return $this->response->created();
    }

    public function getHistory()
    {
        $chats = Chats::where('user_a_id', $this->request->self_user_id)->orWhere('user_b_id', $this->request->self_user_id)->get();
        $info = array();
        foreach($chats as $chat)
        {
            $with_user_id = ($this->request->self_user_id == $chat->user_a_id) ? $chat->user_b_id:$chat->user_a_id;
            $unread_count = ($this->request->self_user_id == $chat->user_a_id) ? $chat->user_a_unread_count:$chat->user_b_unread_count;
            $info[] = ['chat_id' => $chat->id, 'with_user_id' => $with_user_id, 'last_message' => $chat->last_message, 'last_message_sender_id' => $chat->last_message_sender_id, 'last_message_type' => $chat->last_message_type, 'last_message_timestamp' => $chat->last_message_timestamp->format('Y-m-d H:i:s'), 'unread_count' => $unread_count];
        }
        return $this->response->array($info);
    }

    public function delete($chat_id) 
    {
        $input = ['chat_id' => $chat_id];
        $validator = Validator::make($input, [
            'chat_id' => 'required|integer|exists:chats,id',
        ]);
        if($validator->fails())
        {
            throw new DeleteResourceFailedException('Could not delete chat.',$validator->errors());
        }

        $chat = Chats::find($chat_id);
        if(is_null($chat))
        {
            return $this->response->errorNotFound();
        }
        if($this->request->self_user_id != $chat->user_a_id && $this->request->self_user_id != $chat->user_b_id)
        {
            throw new UnauthorizedHttpException(null, 'Bad request, you have no right to delete this chat');
        }
        $chat->delete();
        return $this->response->noContent();
    }

    private function sendValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'type' => 'required|in:text,image',
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not send message.',$validator->errors());
        }
    }
}

