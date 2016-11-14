<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pin_comments extends Model
{
    protected $table = 'pin_comments';
    public function hasManyPinCommentOperations() 
    {
        return $this->hasMany('App\PinCommentOperations','pin_comment_id','id');
    }
    public function delete()
    {
    	$this->hasManyPinCommentOperations()->delete();
    	return parent::delete();
    }
}