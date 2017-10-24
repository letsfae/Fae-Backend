<?php

namespace App\Api\v1\Controllers;
use Validator;
use Config;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use App\Api\v1\Interfaces\PinInterface;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use App\Api\v1\Controllers\PinOperationController;
use App\Api\v1\Controllers\FileController;
use App\Users;
use App\Locations;
use App\PinHelper;
use App\Collection_of_pins;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\Geometry;
use App\Api\v1\Utilities\ErrorCodeUtility;
use App\Api\v1\Utilities\PinUtility;

class LocationController extends Controller implements PinInterface
{
    use Helpers;
    use PostgisTrait;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function create()
    {
        $this->createValidation($this->request);
        $location = new Locations();
        if($this->request->has('file_ids')) {
            if(!FileController::existsByString($this->request->file_ids))
            {
                return response()->json([
                        'message' => 'files not found',
                        'error_code' => ErrorCodeUtility::FILE_NOT_FOUND,
                        'status_code' => '404'
                    ], 404);
            }
            FileController::refByString($this->request->file_ids);
        }
        $location->file_ids = $this->request->file_ids;
        $location->content = $this->request->content;
        $location->geolocation = new Point($this->request->geo_latitude,$this->request->geo_longitude);
        $location->user_id = $this->request->self_user_id;
        $location->save();
        return $this->response->created(null, array('location_id' => $location->id));
    }

    public function update($location_id)
    {
        if(!is_numeric($location_id))
        {
            return response()->json([
                    'message' => 'location_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        $this->updateValidation($this->request);
        $location = Locations::find($location_id);
        if(is_null($location))
        {
            return response()->json([
                    'message' => 'location not found',
                    'error_code' => ErrorCodeUtility::LOCATIONS_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        if($location->user_id != $this->request->self_user_id)
        {
            return response()->json([
                    'message' => 'You can not update this location',
                    'error_code' => ErrorCodeUtility::NOT_OWNER_OF_PIN,
                    'status_code' => '403'
                ], 403);
        }
        if($this->request->has('content'))
        {
            $location->content = $this->request->content;
        }
        if($this->request->has('geo_latitude') && $this->request->has('geo_longitude'))
        {
            $location->geolocation = new Point($this->request->geo_latitude,$this->request->geo_longitude);
        }
        if($this->request->has('file_ids'))
        {
            if($this->request->file_ids == 'null')
            {
                FileController::updateRefByString($location->file_ids, $this->request->file_ids);
                $location->file_ids = null;
            }
            else if(FileController::existsByString($this->request->file_ids))
            {
                FileController::updateRefByString($location->file_ids, $this->request->file_ids);
                $location->file_ids = $this->request->file_ids;
            }
            else
            {
                return response()->json([
                    'message' => 'file not found',
                    'error_code' => ErrorCodeUtility::FILE_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
            }
        }
        $location->save();
        return $this->response->created();
    }

    public function getOne($location_id)
    {
        if(!is_numeric($location_id))
        {
            return response()->json([
                    'message' => 'location_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        $location = $this->getPinObject($location_id, $this->request->self_user_id);
        if(is_null($location)) {
            return response()->json([
                    'message' => 'location not found',
                    'error_code' => ErrorCodeUtility::LOCATIONS_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        return $this->response->array($location);
    }    

    public function delete($location_id)
    {
        if(!is_numeric($location_id))
        {
            return response()->json([
                    'message' => 'location_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        $location = Locations::find($location_id);
        if(is_null($location))
        {
            return response()->json([
                    'message' => 'location not found',
                    'error_code' => ErrorCodeUtility::LOCATIONS_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        if($location->user_id != $this->request->self_user_id)
        {
            return response()->json([
                    'message' => 'You can not delete this location',
                    'error_code' => ErrorCodeUtility::NOT_OWNER_OF_PIN,
                    'status_code' => '403'
                ], 403);
        }
        FileController::derefByString($location->file_ids);
        PinOperationController::deletePinOperations('location', $location->id);
        PinOperationController::deletePinComments('location', $location->id);
        Collection_of_pins::where('type', 'location')->where('pin_id', $location->id)->delete();
        $location->delete();
        return $this->response->noContent();
    }

    public function getFromUser($user_id)
    {
        if(!is_numeric($user_id))
        {
            return response()->json([
                    'message' => 'user_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        if (is_null(Users::find($user_id)))
        {
            return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        $this->getFromUserValidation($this->request);
        $start_time = $this->request->has('start_time') ? $this->request->start_time : '1970-01-01 00:00:00';
        $end_time = $this->request->has('end_time') ? $this->request->end_time : date("Y-m-d H:i:s");
        $page =  $this->request->has('page') ? $this->request->page : 1;
        $locations = array();
        $total = 0;
        $locations = Locations::where('user_id', $user_id)
                        ->where('created_at','>=', $start_time)
                        ->where('created_at','<=', $end_time)
                        ->orderBy('created_at', 'desc')
                        ->skip(30 * ($page - 1))->take(30)->get();
        $total = Locations::where('user_id', $user_id)
                        ->where('created_at','>=', $start_time)
                        ->where('created_at','<=', $end_time)
                        ->count();
        $total_pages = 0;
        if($total > 0)
        {
            $total_pages = intval(($total-1)/30)+1;
        }
        $info = array();
        foreach ($locations as $location)
        {
            $info[] = array(
                'location_id' => $location->id,
                'content' => $location->content, 
                'geolocation' => ['latitude' => $location->geolocation->getLat(), 
                'longitude' => $location->geolocation->getLng()], 
                'file_ids' => $location->file_ids,
                'saved_count' => $location->saved_count,
                'user_pin_operations' => PinOperationController::getOperations('location', $location->id, $user_id),
                'created_at' => $location->created_at->format('Y-m-d H:i:s')
                );   
        }
        return $this->response->array($info)->header('page', $page)->header('total_pages', $total_pages);
    }

    public function getFromCurrentUser()
    {
        return $this->getFromUser($this->request->self_user_id);
    }

    public static function getPinObject($location_id, $user_id) {
        $location = Locations::find($location_id);
        if(is_null($location))
        {
            return null;
        }
        return array(
            'location_id' => $location->id,
            'content' => $location->content, 
            'geolocation' => ['latitude' => $location->geolocation->getLat(), 
            'longitude' => $location->geolocation->getLng()], 
            'file_ids' => $location->file_ids,
            'saved_count' => $location->saved_count,
            'user_pin_operations' => PinOperationController::getOperations('location', $location_id, $user_id),
            'created_at' => $location->created_at->format('Y-m-d H:i:s')
        );
    }

    private function createValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'geo_longitude' => 'required|numeric|between:-180,180',
            'geo_latitude' => 'required|numeric|between:-90,90',
            'file_ids' => 'filled|regex:/^(\d+\;){0,5}\d+$/',
            'content' => 'required|string'
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not create location.',$validator->errors());
        }
    }

    private function updateValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'geo_longitude' => 'filled|required_with:geo_latitude|required_without:content,file_ids|numeric|between:-180,180',
            'geo_latitude' => 'filled|required_with:geo_longitude|required_without:content,file_ids|numeric|between:-90,90',
            'content' => 'filled|required_without_all:geo_longitude,geo_latitude,file_ids|string',
            'file_ids' => 'required|required_without_all:geo_longitude,geo_latitude,content|regex:/^(\d+\;){0,5}\d+$/'
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not update location.',$validator->errors());
        }
    }

    private function getFromUserValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'filled|date_format:Y-m-d H:i:s|before:tomorrow',
            'end_time' => 'filled|date_format:Y-m-d H:i:s|before:tomorrow',
            'page' => 'filled|integer|min:1'
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not get user medias.',$validator->errors());
        }
    }
}

