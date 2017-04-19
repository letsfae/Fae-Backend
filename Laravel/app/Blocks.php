<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Blocks extends Model
{
    protected $table = 'blocks';
    protected $fillable = array('user_id', 'block_id');
}
