<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;

class Locations extends Model
{
    use PostgisTrait;

    protected $table = 'locations';

    protected $postgisFields = [
        'geolocation' => Point::class,
    ];

    public function belongsToUser() 
    {
    	return $this->belongsTo('App\Users','user_id','id');
    }
}
