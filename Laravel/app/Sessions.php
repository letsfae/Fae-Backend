<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;

class Sessions extends Model
{
	use PostgisTrait;

    protected $table = 'sessions';

    protected $postgisFields = [
        'location' => Point::class,
    ];

    public function users() 
    {
    	return $this->belongsTo('App\Users','user_id','id');
    } 
    public function updateLocationTimestamp()
    {
        $this->location_updated_at = $this->freshTimestamp();
        return $this->save();
    }
}
