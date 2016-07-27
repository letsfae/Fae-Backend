<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;

class Faevors extends Model
{
    use PostgisTrait;

    protected $table = 'faevors';

    protected $postgisFields = [
        'geolocation' => Point::class,
    ];

    public function users() 
    {
    	return $this->belongsTo('App\Users','user_id','id');
    }
}
