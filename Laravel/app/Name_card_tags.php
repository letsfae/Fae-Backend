<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Name_card_tags extends Model
{ 
	public $timestamps = false;
    protected $table = 'name_card_tags';
    protected $fillable = array('title', 'color'); 
}
