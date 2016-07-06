<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class Users extends Model implements AuthenticatableContract
{
	use Authenticatable;
    protected $table = 'users';
    public function hasManySessions() 
    {
    	return $this->hasMany('App\Sessions','user_id','id');
    }
    public function hasOneProfile() 
    {
    	return $this->hasOne('App\Profiles','user_id','id');
    }

	protected $fillable = array('email', 'password', 'user_name', 'first_name', 'last_name', 'gender', 'birthday', 'login_count');
    public function comments() {
    	return $this->hasMany('App\Comments');
    }
}
