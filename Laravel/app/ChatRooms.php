<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;

class ChatRooms extends Model
{
	use PostgisTrait;
    protected $table = 'chat_rooms';
    protected $postgisFields = [
        'geolocation' => Point::class,
    ];
    protected $fillable = array('user_id', 'title', 'geolocation', 'last_message_sender_id', 'last_message', 'last_message_timestamp', 'last_message_type');
}
