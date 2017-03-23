<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;

class Businesses extends Model
{
	use PostgisTrait;

    protected $connection = 'yelp';
    protected $table = 'businesses';
    protected $postgisFields = [
        'geolocation' => Point::class,
    ];
    
}
