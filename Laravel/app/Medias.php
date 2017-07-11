<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;

class Medias extends Model
{
    use PostgisTrait;

    protected $table = 'medias';

    protected $postgisFields = [
        'geolocation' => Point::class,
    ];

    public function users() 
    {
    	return $this->belongsTo('App\Users','user_id','id');
    }
    protected $fillable = array('user_id', 'description', 'geolocation', 'tag_ids', 'file_ids', 'duration', 'interaction_radius', 'anonymous','saved_count','liked_count','comment_count','feeling_count');
}
