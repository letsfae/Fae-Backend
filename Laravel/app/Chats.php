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
}
