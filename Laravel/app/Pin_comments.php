<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pin_comments extends Model
{
    protected $table = 'pin_comments';
    protected $fillable = array('user_id', 'pin_id', 'type', 'content');
}