<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hashtags extends Model
{
    protected $table = 'hashtags';
    
    
    /**
     * Get all of the tagets that are assigned this hashtag.
     */
    public function target_obj($className)
    {
        return $this->morphedByMany('App\\'.$className, 'hashtaggable');
    }
    
    public function comments()
    {
        return $this->morphedByMany('App\\Comments', 'hashtaggable');
    }
}