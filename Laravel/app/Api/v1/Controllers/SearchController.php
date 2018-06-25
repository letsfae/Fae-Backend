<?php
namespace App\Api\v1\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use App\Users;
use App\Api\v1\Utilities\ErrorCodeUtility;
use Dingo\Api\Exception\StoreResourceFailedException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Validator;
use Elasticsearch;

class SearchController extends Controller {
    use Helpers;
    public function __construct(Request $request) {
        $this->request = $request;
    }

    private function singleSearch($postbody){
        $this->searchValidation($postbody);

        $size = (array_key_exists('size', $postbody))?$postbody["size"]:10;
        $offset = (array_key_exists('offset', $postbody))?$postbody["offset"]:0;

        $data = [
            "body" => [
                "size" => $size,
                "from" => $offset,
                "query"=> [
                    "filtered"=> [
                        "query"=> [
                            "match" => [
                                $postbody["source"] => [
                                    "query" => $postbody["content"],
                                    "fuzziness" => 3,
                                    "prefix_length" => 2,
                                ]

                            ]
                        ]
                    ]
                ]

            ],
            "index" => "foursquare",
            "type" => "places"
        ];

        if(array_key_exists('location', $postbody)){
            $longitude = $postbody["location"]["longitude"];
            $latitude = $postbody["location"]["latitude"];
            $radius = $postbody["radius"];

            $data["body"]["query"]["filtered"]["filter"] = [
                        "geo_distance" => [
                            "distance" => $radius."m",
                            "distance_type" => "sloppy_arc", 
                            "places.location"=> [
                                "lat" => $latitude,
                                "lon" => $longitude
                            ]
                        ]
            ];
        }

        // if(array_key_exists('filter', $postbody)){
        //     foreach ($postbody["filter"] as $key => $value){
        //         print_r($value);
        //         $data["body"]["query"]["filtered"]["filter"]["bool"]["should"] +=[
        //             "class_one" =>"Little Sister"
        //         ];
        //          $data["body"]["query"]["filtered"]["filter"]["bool"] += ["minimum_should_match"=>2];
        //     }
        // }
        //print_r($data);

        if(array_key_exists('sort', $postbody)){
            $data["body"]["sort"] = $postbody["sort"];
            foreach ($postbody["sort"] as $key => $sort) {
                if(array_key_exists('geo_location', $sort)){
                    if(array_key_exists('location', $postbody)){
                        $data["body"]["sort"][$key] = array(
                        "_geo_distance" => [
                            "places.location" => [
                                "lat" => $latitude, 
                                "lon" => $longitude
                            ],
                            "order" => $sort["geo_location"],
                            "unit" => "m",
                            "distance_type" => "sloppy_arc", 
                            "places.location" => [
                                "lat" =>  $latitude,
                                "lon" => $longitude
                            ]
                        ]);
                    }else{
                        array_splice($data["body"]["sort"], $key, 1);
                    }
                    
                }
            }

        }

        $raw_places = Elasticsearch::search($data)['hits']['hits'];

        $places = array();
        foreach ($raw_places as $place) {
            $places[] = PlaceController::getPinObject($place['_source']['id'], $this->request->self_user_id);
        }

        return $places;
    }

    public function search() {
        // Check for presence of a body in the request
        if (count($this->request->json()->all())) {
            $postbody = $this->request->json()->all();
        }else{
            throw new StoreResourceFailedException('Could not search, search body invalid.');
        }

        return $this->response->array($this->singleSearch($postbody)); 
    }

    public function bulk() {
        if (count($this->request->json()->all())) {
            $postbody = $this->request->json()->all();
        }else{
            throw new StoreResourceFailedException('Could not search, search body invalid.');
        }

        $places = array();
        foreach ($postbody as $post) {
            $places[] = $this->singleSearch($post);
        }

        return $this->response->array($places);
    }

    private function searchValidation($postbody){
        $validator = Validator::make($postbody, [
            'type' => 'required|string|in:place',
            'content' => 'required|string',
            'radius' => 'required_with:location',
            'size' => 'integer',
            'offset' => 'integer',
            'source' => 'required|string|in:categories,name,class_one',

        ]);
        if($validator->fails()){
            throw new StoreResourceFailedException('Could not search.',$validator->errors());
        }

        if (array_key_exists('location', $postbody)){
            $validator = Validator::make($postbody["location"], [
                'latitude' => 'required|numeric|between:-180,180',
                'longitude' => 'required|numeric|between:-180,180',
            ]);
            if($validator->fails()){
                throw new StoreResourceFailedException('Could not search.',$validator->errors());
            }
        }

        if (array_key_exists('sort', $postbody)){
            $validator = Validator::make($postbody, [
                'sort.name' => 'string|between:desc, asc',
                'sort.geo_location' => 'string|between:desc, asc',
            ]);
            if($validator->fails()){
                throw new StoreResourceFailedException('Could not search.',$validator->errors());
            }
        }

        if (array_key_exists('filter', $postbody)){
            $validator = Validator::make($postbody, [
                'filter.class_one' => 'string',
            ]);
            if($validator->fails()){
                throw new StoreResourceFailedException('Could not search.',$validator->errors());
            }
        }
        
    }
}
