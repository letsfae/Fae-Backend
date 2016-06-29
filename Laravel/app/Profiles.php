<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class profiles extends Model
{
    protected $table = 'profiles';
    public $timestamps = false;
    public $incrementing = false;
    public $primaryKey = 'user_id';
    public function hasOneUser() {
    	return $this->hasOne('App\Users','id','user_id');
    }
}
