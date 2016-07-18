<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Name_cards extends Model
{
    protected $table = 'name_cards';
    public $timestamps = false;
    public $incrementing = false;
    public $primaryKey = 'user_id';
    public function hasOneUser()
    {
    	return $this->hasOne('App\Users','id','user_id');
    }
}
