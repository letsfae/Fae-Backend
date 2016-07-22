<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chats extends Model
{
    protected $table = 'chats';
    public function updateTimestamp()
    {
        $this->last_message_timestamp = $this->freshTimestamp();
        return $this->save();
    }
    protected $fillable = array('user_a_id', 'user_b_id', 'last_message_sender_id', 'last_message', 'last_message_type', 'user_a_unread_count', 'user_b_unread_count', 'last_message_timestamp');
}
