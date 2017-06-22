<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;

class Places extends Model
{
	use PostgisTrait;

    protected $connection = 'foursquare';
    protected $table = 'places';
    public $timestamps = false;
    protected $postgisFields = [
        'geolocation' => Point::class,
    ];
    
}
