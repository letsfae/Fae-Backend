<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Collections extends Model
{
    protected $table = 'collections';
    public function belongsToUser() 
    {
    	return $this->belongsTo('App\Users','user_id','id');
    }
    public function updateLastUpdatedAt()
    {
        $this->last_updated_at = $this->freshTimestamp();
        return $this->save();
    }
}
