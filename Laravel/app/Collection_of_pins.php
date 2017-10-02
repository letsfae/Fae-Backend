<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Collection_of_pins extends Model
{
    protected $table = 'collection_of_pins';

    public function belongsToCollection() 
    {
    	return $this->belongsTo('App\Collections','collection_id','id');
    }
}
