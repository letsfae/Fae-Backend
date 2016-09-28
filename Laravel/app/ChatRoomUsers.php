<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatRoomUsers extends Model
{
    protected $table = 'chat_room_users';
    public function belongsToChatRoom() 
    {
    	return $this->belongsTo('App\ChatRooms','chat_room_id','id');
    }
    public function belongsToUser() 
    {
    	return $this->belongsTo('App\ChatRooms','user_id','id');
    }
}
