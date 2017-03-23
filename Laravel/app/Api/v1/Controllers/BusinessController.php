<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Api\v1\Interfaces\PinInterface;

use App\Api\v1\Utilities\ErrorCodeUtility;
use App\Businesses;

class BusinessController extends Controller implements PinInterface {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function create() {

    }

    public function update($business_id) {

    }

    public function delete($business_id) {

    }

     public static function getPinObject($business_id, $user_id) {
        $business = Businesses::find($business_id);
        if(is_null($business))
        {
            return null;
        }

        $categories = explode(', ', $business->categories);

        return array(
            'business_id' => $business->id, 
            'name' => $business->name,
            'categories' => $categories,
            'geolocation' => ['latitude' => $business->geolocation->getLat(), 
            'longitude' => $business->geolocation->getLng()], 
            'country' => $business->country, 
            'state' => $business->state,
            'address' => $business->address, 
            'zip_code' => $business->zip_code
        );
     }

     public function getOne($business_id) {
        if(!is_numeric($business_id)){
            return response()->json([
                    'message' => 'business_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }

        $business = $this->getPinObject($business_id, $this->request->self_user_id);
        if(is_null($business))
        {
            return response()->json([
                    'message' => 'business not found',
                    //'error_code' => ErrorCodeUtility::CHAT_ROOM_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        return $this->response->array($business);
     }

    public function getImage($business_id) {
        if(!is_numeric($business_id)){
            return response()->json([
                    'message' => 'business_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }

        try {
            $file = Storage::disk('local')->get('businessPicture/'.$business_id.'.jpg');
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
