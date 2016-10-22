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

    public function hasManyMedias() 
    {
        return $this->hasMany('App\Medias','user_id','id');
    }

    public function hasManyFaevors() 
    {
        return $this->hasMany('App\Faevors','user_id','id');
    }
    
    public function hasManyChatRoomUsers() 
    {
        return $this->hasMany('App\ChatRoomUsers','user_id','id');
    }
    public function hasOneExts() 
    {
        return $this->hasOne('App\User_exts','user_id','id');
    }
    
    /**
     * Get all of the tagets that has at this user.
     */
    public function target_obj($className)
    {
        return $this->morphedByMany('App\\'.$className, 'useratable');
    }
}
