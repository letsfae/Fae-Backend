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
}
