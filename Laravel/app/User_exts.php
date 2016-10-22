<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_exts extends Model
{
    protected $table = 'user_exts';
    public $timestamps = false;
    public $incrementing = false;
    public $primaryKey = 'user_id';
    public function hasOneUser()
    {
    	return $this->hasOne('App\Users','id','user_id');
    }
}
