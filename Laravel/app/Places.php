<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Places extends Eloquent {

    protected $connection = 'mongodb';
    protected $collection = 'places';
    protected $primaryKey = 'id';
    //protected $table = 'places';   
   
}
