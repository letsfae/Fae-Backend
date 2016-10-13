<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;

class PinHelper extends Model
{
    use PostgisTrait;
    protected $table = 'pin_helper';
	protected $postgisFields = [
	  'geolocation' => Point::class,
	];
}
