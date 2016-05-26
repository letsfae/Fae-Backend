<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sessions extends Model
{
    protected $table = 'sessions';
    public function users() {
    	return $this->belongsTo('App\Users');
    } 
}
