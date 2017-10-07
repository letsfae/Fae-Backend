<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Api\v1\Interfaces\PinInterface;

use App\Api\v1\Utilities\ErrorCodeUtility;
use App\Places;
use DB;

class PlaceController extends Controller implements PinInterface {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function create() {

    }

    public function update($place_id) {

    }

    public function delete($place_id) {

    }

     public static function getPinObject($place_id, $user_id) {
        $place = Places::find($place_id);
        if(is_null($place))
        {
            return null;
        }

        $categories = explode(',', $place->categories);

        return array(
            'place_id' => $place->id, 
            'name' => $place->name,
            'geolocation' => [
                'latitude' => $place->geolocation->getLat(), 
                'longitude' => $place->geolocation->getLng()], 
            'location' => [
                'city' => $place->city,
                'country' => $place->country, 
                'state' => $place->state,
                'address' => $place->address, 
                'zip_code' => $place->zip_code],
            'categories' => [
                'class1' => $place->class_one,
                'class1_icon_id' => $place->class_one_idx,
                'class2' => $place->class_two,
                'class2_icon_id' => $place->class_two_idx,
                'class3' => $place->class_three,
                'class3_icon_id' => $place->class_three_idx,
                'class4' => $place->class_four,
                'class4_icon_id' => $place->class_four_idx]
        );
     }

     public function getOne($place_id) {
        if(!is_numeric($place_id)){
            return response()->json([
                    'message' => 'place_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }

        $place = $this->getPinObject($place_id, $this->request->self_user_id);
        if(is_null($place))
        {
            return response()->json([
                    'message' => 'place not found',
                    //'error_code' => ErrorCodeUtility::CHAT_ROOM_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        return $this->response->array($place);
     }

    public function getImage($place_id) {
        if(!is_numeric($place_id)){
            return response()->json([
                    'message' => 'place_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }

        try {
            $file = Storage::disk('local')->get('placePicture/'.$place_id.'.jpg');
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'Bad request, image not found',
                'status_code' => '404',
            ], 404);
        }
        return response($file, 200)->header('Content-Type', 'image/jpeg');
    }

    public function getFromUser($user_id){

    }
}
