<?php

namespace App\Api\v1\Controllers; 
use Illuminate\Http\Request;  
use App\Api\v1\Interfaces\PinInterface; 
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Validator;
use App\ChatRooms;
use App\ChatRoomUsers; 
use Config; 
use App\Users;
use APP\Sessions;
use DB;
use App\PinHelper;



class ChatRoomController extends Controller implements PinInterface 
{
    use Helpers;
    use PostgisTrait;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function create()
    {
        $this->createValidation($this->request);
        $chat_room = new ChatRooms();
        $chat_room->title = $this->request->title;
        $chat_room->geolocation = new Point($this->request->geo_latitude, $this->request->geo_longitude);
        $chat_room->user_id = $this->request->self_user_id;
        $chat_room->duration = $this->request->duration;
        if($this->request->has('interaction_radius'))
        {
            $chat_room->interaction_radius = $this->request->interaction_radius;
        }
        $chat_room->save();
        $chat_room_user = new ChatRoomUsers();
        $chat_room_user->user_id = $this->request->self_user_id;
        $chat_room_user->chat_room_id = $chat_room->id;
        $chat_room_user->save();
        $pin_helper = new PinHelper();
        $pin_helper->type = 'chat_room';
        $pin_helper->user_id = $this->request->self_user_id;
        $pin_helper->geolocation =  new Point($this->request->geo_latitude, $this->request->geo_longitude);
        $pin_helper->pin_id = $chat_room->id;
        $pin_helper->duration = $chat_room->duration;
        $pin_helper->save();
        return $this->response->created(null, array('chat_room_id' => $chat_room->id));
    }

    public function update($chat_room_id)
    {
        if(!is_numeric($chat_room_id))
        {
            return $this->response->errorBadRequest();
        }
        $this->updateValidation($this->request);
        $chat_room = ChatRooms::find($chat_room_id);
        if(is_null($chat_room))
        {
            return $this->response->errorNotFound();
        }
        if($chat_room->user_id != $this->request->self_user_id)
        {
            throw new AccessDeniedHttpException('You can not update this chat room');
        }
        if($this->request->has('title'))
        {
            $chat_room->title = $this->request->title;
        }
        if($this->request->has('geo_latitude') && $this->request->has('geo_longitude'))
        {
            $chat_room->geolocation = new Point($this->request->geo_latitude, $this->request->geo_longitude);
            $pin_helper = PinHelper::where('pin_id', $chat_room_id)->where('type', 'chat_room')->first();
            $pin_helper->geolocation = new Point($this->request->geo_latitude, $this->request->geo_longitude);
            $pin_helper->save();
        }
        if($this->request->has('duration'))
        {
            $chat_room->duration = $this->request->duration; 
            $pin_helper = PinHelper::where('pin_id', $hat_room_id)->where('type', 'chat_room')->first();
            $pin_helper->duration = $hat_room->duration; 
            $pin_helper->save();
        }
        if($this->request->has('interaction_radius'))
        {
            $chat_room->interaction_radius = $this->request->interaction_radius;
        }
        $chat_room->save();
        return $this->response->created();
    }

    public function getOne($chat_room_id)
    {
        if(!is_numeric($chat_room_id))
        {
            return $this->response->errorBadRequest();
        }
        $chat_room = ChatRooms::find($chat_room_id);
        if(is_null($chat_room))
        {
            return $this->response->errorNotFound();
        }
        return $this->response->array(array('chat_room_id' => $chat_room->id, 'title' => $chat_room->title, 'user_id' => $chat_room->user_id,
            'geolocation' => ['latitude' => $chat_room->geolocation->getLat(), 'longitude' => $chat_room->geolocation->getLng()], 
            'last_message' => $chat_room->last_message, 'last_message_sender_id' => $chat_room->last_message_sender_id,
            'last_message_type' => $chat_room->last_message_type, 'last_message_timestamp' => $chat_room->last_message_timestamp, 
            'created_at' => $chat_room->created_at->format('Y-m-d H:i:s')));
    }

    public function delete($chat_room_id)
    {
        // non use
    }

    public function getFromUser($user_id)
    {
        if(!is_numeric($user_id))
        {
            return $this->response->errorBadRequest();
        }
        if (is_null(Users::find($user_id)))
        {
            return $this->response->errorNotFound();
        }
        $this->getFromUserValidation($this->request);
        $start_time = $this->request->has('start_time') ? $this->request->start_time : '1970-01-01 00:00:00';
        $end_time = $this->request->has('end_time') ? $this->request->end_time : date("Y-m-d H:i:s");
        $page =  $this->request->has('page') ? $this->request->page : 1;
        $chat_rooms = ChatRooms::where('user_id', $user_id)->where('created_at','>=', $start_time)->where('created_at','<=', $end_time)->orderBy('created_at', 'desc')->skip(30 * ($page - 1))->take(30)->get();
        $total_pages = intval($chat_rooms->count() / 30) + 1; 
        $total = ChatRooms::where('user_id', $user_id)->where('created_at','>=', $start_time)
                    ->where('created_at','<=', $end_time)->count();
        $total_pages = 0;
        if($total > 0)
        {
            $total_pages = intval(($total-1)/30)+1;
        } 
        $info = array();
        foreach($chat_rooms as $chat_room)
        {
            $info[] = array('chat_room_id' => $chat_room->id, 'title' => $chat_room->title, 
            'user_id' => $chat_room->user_id,'geolocation' => ['latitude' => $chat_room->geolocation->getLat(), 
            'longitude' => $chat_room->geolocation->getLng()], 'last_message' => $chat_room->last_message, 
            'last_message_sender_id' => $chat_room->last_message_sender_id, 'last_message_type' => $chat_room->last_message_type, 
            'last_message_timestamp' => $chat_room->last_message_timestamp, 'created_at' => $chat_room->created_at->format('Y-m-d H:i:s'));
        }
        return $this->response->array($info)->header('page', $page)->header('total_pages', $total_pages);
    }

    public function send($chat_room_id)
    {
        if(!is_numeric($chat_room_id))
        {
            return $this->response->errorBadRequest();
        }
        $this->sendValidation($this->request);
        $chat_room = ChatRooms::find($chat_room_id);
        if(is_null($chat_room))
        {
            return $this->response->errorNotFound();
        }
        $chat_room_user = ChatRoomUsers::where('chat_room_id', $chat_room_id)->where('user_id', $this->request->self_user_id)->first();
        if(is_null($chat_room_user))
        {
            $session = Sessions::find($this->request->self_session_id);
            if(is_null($session) || !$session->is_mobile || $session->location == null)
            {
                return $this->response->errorNotFound();
            }
            $distance = DB::select("SELECT ST_Distance_Spheroid(ST_SetSRID(ST_Point(:longitude1, :latitude1),4326), 
                ST_SetSRID(ST_Point(:longitude2, :latitude2),4326), 'SPHEROID[\"WGS 84\",6378137,298.257223563]')",
                array('longitude1' => $session->location->getLng(), 'latitude1' => $session->location->getLat(),
                      'longitude2' => $chat_room->geolocation->getLng(), 'latitude2' => $chat_room->geolocation->getLat()));
            if($distance[0]->st_distance_spheroid > ($chat_room->interaction_radius))
            {
                return $this->response->errorBadRequest('too far away');
            }
            $new_chat_room_user = new ChatRoomUsers();
            $new_chat_room_user->chat_room_id = $chat_room_id;
            $new_chat_room_user->user_id = $this->request->self_user_id;
            $new_chat_room_user->save();
        }
        if(!is_null($chat_room_user) && $chat_room_user->unread_count > 0)
        {
            return $this->response->errorBadRequest('Please mark unread messages before sending new messages!');
        }

        $chat_room->last_message_sender_id = $this->request->self_user_id;
        $chat_room->last_message = $this->request->message;
        $chat_room->last_message_type = $this->request->type;
        $chat_room->updateTimestamp();
        $chat_room->save();
        
        $chat_room_users = ChatRoomUsers::where('chat_room_id', $chat_room_id)->where('user_id', '!=', $this->request->self_user_id)->get();
        foreach ($chat_room_users as $chat_room_user)
        {
            $chat_room_user->unread_count++;
            $chat_room_user->save();
        }
        return $this->response->created();
    }

    public function getUnread()
    {
        $chat_room_users = ChatRoomUsers::where('user_id', $this->request->self_user_id)->where('unread_count', '>', 0)->get();
        $info = array();
        foreach($chat_room_users as $chat_room_user)
        {
            $chat_room = $chat_room_user->belongsToChatRoom()->first();
            $time = date("Y-m-d H:i:s");
            $last_message_sender  = Users::find($chat_room->last_message_sender_id);
            if(is_null($last_message_sender))
            {
                return $this->errorNotFound();
            }
            $info[] = array('chat_room_id' => $chat_room->id, 'title' => $chat_room->title, 'user_id' => $chat_room->user_id,
            'geolocation' => ['latitude' => $chat_room->geolocation->getLat(), 'longitude' => $chat_room->geolocation->getLng()], 
            'last_message' => $chat_room->last_message, 'last_message_sender_id' => $chat_room->last_message_sender_id, 
            'last_message_sender_name' => $last_message_sender->user_name,
            'last_message_type' => $chat_room->last_message_type, 'last_message_timestamp' => $chat_room->last_message_timestamp, 
            'unread_count' => $chat_room_user->unread_count, 'created_at' => $chat_room->created_at->format('Y-m-d H:i:s'), 
            'server_sent_timestamp' => $time);
        }
        $filed = array();
        foreach ($info as $key => $value)
        {
            $filed[$key] = $value['last_message_timestamp'];
        }
        array_multisort($filed, SORT_DESC, $info);
        return $this->response->array($info);
    }

    public function markRead($chat_room_id)
    {
        if(!is_numeric($chat_room_id))
        {
            return $this->response->errorBadRequest();
        }
        $chat_room_user = ChatRoomUsers::where('chat_room_id', $chat_room_id)->where('user_id', $this->request->self_user_id)->first();
        if(is_null($chat_room_user))
        {
            return $this->response->errorNotFound();
        }
        $chat_room_user->unread_count = 0;
        $chat_room_user->save();
        return $this->response->created();
    }

    public function getHistory()
    {
        $user = Users::find($this->request->self_user_id);
        $chat_room_users = $user->hasManyChatRoomUsers()->get();
        $info = array();
        $time = date("Y-m-d H:i:s");
        foreach ($chat_room_users as $chat_room_user)
        {
            $chat_room = $chat_room_user->belongsToChatRoom()->first();
            $last_message_sender = Users::find($chat_room->last_message_sender_id);
            if(is_null($last_message_sender))
            {
                return $this->errorNotFound();
            }
            $info[] = array('chat_room_id' => $chat_room->id, 'title' => $chat_room->title, 'user_id' => $chat_room->user_id,
            'geolocation' => ['latitude' => $chat_room->geolocation->getLat(), 'longitude' => $chat_room->geolocation->getLng()], 
            'last_message' => $chat_room->last_message, 'last_message_sender_id' => $chat_room->last_message_sender_id, 
            'last_message_sender_name' => $last_message_sender->user_name,
            'last_message_type' => $chat_room->last_message_type, 'last_message_timestamp' => $chat_room->last_message_timestamp, 
            'unread_count' => $chat_room_user->unread_count, 'created_at' => $chat_room->created_at->format('Y-m-d H:i:s'), 
            'server_sent_timestamp' => $time);
        }
        $filed = array();
        foreach ($info as $key => $value)
        {
            $filed[$key] = $value['last_message_timestamp'];
        }
        array_multisort($filed, SORT_DESC, $info);
        return $this->response->array($info);
    }

    public function getUserList($chat_room_id)
    {
        if(!is_numeric($chat_room_id))
        {
            return $this->response->errorBadRequest();
        }
        $chat_room = ChatRooms::find($chat_room_id);
        if(is_null($chat_room))
        {
            return $this->response->errorNotFound();
        }
        $chat_room_users = $chat_room->hasManyChatRoomUsers()->get();
        $info = array();
        foreach ($chat_room_users as $chat_room_user)
        {
            $info[] = array('chat_room_id' => $chat_room_user->chat_room_id, 'user_id' => $chat_room_user->user_id, 
                'created_at' => $chat_room_user->created_at->format('Y-m-d H:i:s'));
        }
        return $this->response->array($info);
    }

    private function createValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'geo_longitude' => 'required|numeric|between:-180,180',
            'geo_latitude' => 'required|numeric|between:-90,90',
            'duration' => 'required|int|min:0',
            'interaction_radius' => 'filled|int|min:0'
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not create chat room.', $validator->errors());
        }
    }

    private function updateValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'filled|required_without_all:geo_longitude,geo_latitude,duration,interaction_radius|string|max:100',
            'geo_longitude' => 'filled|required_with:geo_latitude|required_without_all:title,duration,interaction_radius
                                |numeric|between:-180,180',
            'geo_latitude' => 'filled|required_with:geo_longitude|required_without_all:title,duration,interaction_radius
                                |numeric|between:-90,90',
            'duration' => 'filled|required_without_all:geo_longitude,geo_latitude,title,interaction_radius|int|min:0',
            'interaction_radius' => 'filled|required_without_all:geo_longitude,geo_latitude,duration,title|int|min:0'
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not update chat room.', $validator->errors());
        }
    }

    private function getFromUserValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'filled|date_format:Y-m-d H:i:s|before:tomorrow',
            'end_time' => 'filled|date_format:Y-m-d H:i:s|before:tomorrow',
            'page' => 'filled|integer|min:1'
        ]);
        if($validator->fails())
        { 
            throw new UpdateResourceFailedException('Could not get user chatrooms.',$validator->errors()); 
        }
    }

    private function sendValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
            'type' => 'required|in:text,image,sticker,location,audio',
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not send message.',$validator->errors());
        }
    }
}