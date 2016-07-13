<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Verifications extends Model
{
     protected $table = 'verifications';
     protected $fillable = array('type', 'email', 'phone', 'code', 'created_at');
}
