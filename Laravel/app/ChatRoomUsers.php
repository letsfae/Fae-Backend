<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatRoomUsers extends Model
{
    protected $table = 'chat_room_users';
    protected $fillable = array('chat_room_id', 'user_id', 'unread_count');
    public function belongsToChatRoom() 
    {
    	return $this->belongsTo('App\ChatRooms','chat_room_id','id');
    }
    public function belongsToUser() 
    {
    	return $this->belongsTo('App\ChatRooms','user_id','id');
    }
}
