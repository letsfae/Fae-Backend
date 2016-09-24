<?php

namespace App\Api\v1\Controllers;
use Illuminate\Http\Request;
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

class ChatRoomController extends Controller implements PinInterface {
    use Helpers;

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
        $chat_room->user_id = $this->self_user_id;
        $chat_room->save();
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
        if($chat_room->user_id != $this->self_user_id)
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
        }
        $chat_room->save();
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
        return $this->response->array('chat_room_id' => $chat_room->id, 'title' => $chat_room->title, 'user_id' => $chat_room->user_id,
            'geolocation' => ['latitude' => $faevor->geolocation->getLat(), 'longitude' => $faevor->geolocation->getLng()], 
            'last_message' => $chat_room->last_message, 'last_message_sender_id' => $chat_room->last_message_sender_id,
            'last_message_type' => $chat_room->last_message_type, 'last_message_timestamp' => $chat_room->last_message_timestamp, 
            'created_at' => $chat_room->created_at->format('Y-m-d H:i:s'));
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
        $info = array();
        foreach($chat_rooms as $chat_room)
        {
            $info[] = array('chat_room_id' => $chat_room->id, 'user_id' => $chat_room->user_id, 'title' => $chat_room->title, 
            'user_id' => $chat_room->user_id,'geolocation' => ['latitude' => $faevor->geolocation->getLat(), 
            'longitude' => $faevor->geolocation->getLng()], 'last_message' => $chat_room->last_message, 
            'last_message_sender_id' => $chat_room->last_message_sender_id, 'last_message_type' => $chat_room->last_message_type, 
            'last_message_timestamp' => $chat_room->last_message_timestamp, 'created_at' => $chat_room->created_at->format('Y-m-d H:i:s'));
        }
        return $this->response->array($info)->header('page', $page)->header('total_pages', $total_pages);
    }

    public function send($chat_room_id)
    {

    }

    public function getUnread($chat_room_id)
    {

    }

    public function markRead($chat_room_id)
    {

    }

    public function getHistory()
    {

    }

    public function getUserList($chat_room_id)
    {

    }

    private function createValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'geo_longitude' => 'required|numeric|between:-180,180',
            'geo_latitude' => 'required|numeric|between:-90,90',
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not create chat room.', $validator->errors());
        }
    }

    private function updateValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'filled|required_without_all:geo_longitude,geo_latitude|string|max:100',
            'geo_longitude' => 'filled|required_with:geo_latitude|required_without_all:title|numeric|between:-180,180',
            'geo_latitude' => 'filled|required_with:geo_longitude|required_without_all:title|numeric|between:-90,90',
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
            throw new NotAcceptableHttpException('Could not get chat rooms.', $validator->errors());
        }
    }
}