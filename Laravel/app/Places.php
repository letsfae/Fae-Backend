<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Elasticsearch;

class Places extends Model
{
	// use PostgisTrait;

 //    protected $connection = 'foursquare';
 //    protected $table = 'places';
 //    public $timestamps = false;
 //    protected $postgisFields = [
 //        'geolocation' => Point::class,
 //    ];
    
    public function find($place_id){
        $data = [
            "body" => [
                "query"=> [
                	"bool"=>[
                        "should"=>[
                            "match" => [
			                  "id" => $place_id
			                ]
                        ]
                    ]
                ]
            ],
            "index" => "foursquare",
            "type" => "places"
        ];


        $raw_places = Elasticsearch::search($data)['hits']['hits'];
        $places = array();
        foreach ($raw_places as $place) {
            $places[] = $place['_source'];
        }

        return $places;
    }
    public function store($place_id){
        // Validate the request...

        $flight = new Flight;

        $flight->name = $request->name;

        $flight->save();
    }
}
