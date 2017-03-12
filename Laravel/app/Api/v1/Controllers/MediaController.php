<?php

namespace App\Api\v1\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use App\Api\v1\Interfaces\PinInterface;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Validator;
use App\Medias;
use App\Files;
use App\Tags;
use App\Users;
use App\PinHelper;
use App\Name_cards;
use App\Api\v1\Controllers\PinOperationController;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\Geometry;
use App\Api\v1\Controllers\TagController;
use App\Api\v1\Controllers\FileController;

class MediaController extends Controller implements PinInterface
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
        $media = new Medias();
        if($this->request->has('tag_ids'))
        {
            if(TagController::existsByString($this->request->tag_ids))
            {
                $media->tag_ids = $this->request->tag_ids;
            }
            else
            {
                return response()->json([
                    'message' => 'tags not found',
                    'error_code' => ErrorCodeUtility::TAG_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
            }
        }
        if(!FileController::existsByString($this->request->file_ids))
        {
            return response()->json([
                    'message' => 'files not found',
                    'error_code' => ErrorCodeUtility::FILE_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        FileController::refByString($this->request->file_ids);
        $media->file_ids = $this->request->file_ids;
        $media->description = $this->request->description;
        $media->geolocation = new Point($this->request->geo_latitude,$this->request->geo_longitude);
        $media->user_id = $this->request->self_user_id;
        $media->duration = $this->request->duration;
        $pin_helper = new PinHelper();
        if($this->request->has('interaction_radius'))
        {
            $media->interaction_radius = $this->request->interaction_radius;
        }
        if($this->request->has('anonymous'))
        {
            $media->anonymous = $this->request->anonymous == 'true' ? true : false;
            $pin_helper->anonymous = $media->anonymous;
        }
        $media->save();
        if($this->request->has('tag_ids'))
        {
            TagController::refByString($this->request->tag_ids, $media->id, 'media');
        }
        $pin_helper->type = 'media';
        $pin_helper->user_id = $this->request->self_user_id;
        $pin_helper->geolocation =  new Point($this->request->geo_latitude, $this->request->geo_longitude);
        $pin_helper->pin_id = $media->id;
        $pin_helper->duration = $media->duration;
        $pin_helper->save();
        return $this->response->created(null, array('media_id' => $media->id));
    }

    public function update($media_id)
    {
        if(!is_numeric($media_id))
        {
            return response()->json([
                    'message' => 'media_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        $this->updateValidation($this->request);
        $media = Medias::find($media_id);
        if(is_null($media))
        {
            return response()->json([
                    'message' => 'media not found',
                    'error_code' => ErrorCodeUtility::MEDIA_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        if($media->user_id != $this->request->self_user_id)
        {
            return response()->json([
                    'message' => 'You can not update this media',
                    'error_code' => ErrorCodeUtility::NOT_OWNER_OF_PIN,
                    'status_code' => '403'
                ], 403);
        }
        if($this->request->has('file_ids'))
        { 
            if(FileController::existsByString($this->request->file_ids))
            {
                FileController::updateRefByString($media->file_ids, $this->request->file_ids);
                $media->file_ids = $this->request->file_ids;
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
        if($this->request->has('tag_ids'))
        {
            if($this->request->tag_ids == 'null')
            {
                TagController::updateRefByString($media->tag_ids, $this->request->tag_ids, $media->id, 'media');
                $media->tag_ids = null;
            }
            else if(TagController::existsByString($this->request->tag_ids))
            {
                TagController::updateRefByString($media->tag_ids, $this->request->tag_ids, $media->id, 'media');
                $media->tag_ids = $this->request->tag_ids;
            }
            else
            {
                return response()->json([
                    'message' => 'tag not found',
                    'error_code' => ErrorCodeUtility::TAG_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
            }
        }
        if($this->request->has('description'))
        {
            $media->description = $this->request->description;
        }
        if($this->request->has('geo_latitude') && $this->request->has('geo_longitude'))
        {
            $media->geolocation = new Point($this->request->geo_latitude,$this->request->geo_longitude);
            $pin_helper = PinHelper::where('pin_id', $media_id)->where('type', 'media')->first();
            $pin_helper->geolocation = new Point($this->request->geo_latitude, $this->request->geo_longitude);
            $pin_helper->save();
        }
        if($this->request->has('duration'))
        {
            $media->duration = $this->request->duration;
            $pin_helper = PinHelper::where('pin_id', $media_id)->where('type', 'media')->first();
            $pin_helper->duration = $media->duration;
            $pin_helper->save();
        }
        if($this->request->has('interaction_radius'))
        {
            $media->interaction_radius = $this->request->interaction_radius;
        }
        if($this->request->has('anonymous'))
        {
            $media->anonymous = $this->request->anonymous == 'true' ? true : false;
            $pin_helper = PinHelper::where('pin_id', $media_id)->where('type', 'media')->first();
            $pin_helper->anonymous = $media->anonymous;
            $pin_helper->save();
        }
        $media->save();
        return $this->response->created();
    }

    public function getOne($media_id)
    {
        if(!is_numeric($media_id))
        {
            return response()->json([
                    'message' => 'media_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        $media = Medias::find($media_id);
        if(is_null($media))
        {
            return response()->json([
                    'message' => 'media not found',
                    'error_code' => ErrorCodeUtility::MEDIA_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        $file_ids = is_null($media->file_ids) ? null : explode(';', $media->file_ids);
        $tag_ids = is_null($media->tag_ids) ? null : explode(';', $media->tag_ids);
        $user_pin_operations = PinOperationController::getOperations('media', $media_id, $this->request->self_user_id);
        return $this->response->array(array('media_id' => $media->id, 
            'user_id' => ($media->anonymous && $media->user_id != $this->request->self_user_id) ? null : $media->user_id, 
            'anonymous' => $media->anonymous, 
            'file_ids' => $file_ids, 
            'tag_ids' => $tag_ids, 
            'nick_name' => ($media->anonymous && $media->user_id != $this->request->self_user_id) ? 
                            null : Name_cards::find($commented_pin->user_id)->nick_name,
            'description' => $media->description, 
            'geolocation' => ['latitude' => $media->geolocation->getLat(), 
            'longitude' => $media->geolocation->getLng()], 
            'liked_count' => $media->liked_count, 
            'saved_count' => $media->saved_count, 
            'comment_count' => $media->comment_count,
            'created_at' => $media->created_at->format('Y-m-d H:i:s'),
            'user_pin_operations' => $user_pin_operations));
    }

    public function delete($media_id)
    {
        if(!is_numeric($media_id))
        {
            return response()->json([
                    'message' => 'media_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        $media = Medias::find($media_id);
        if(is_null($media))
        {
            return response()->json([
                    'message' => 'media not found',
                    'error_code' => ErrorCodeUtility::MEDIA_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        if($media->user_id != $this->request->self_user_id)
        {
            return response()->json([
                    'message' => 'You can not delete this media',
                    'error_code' => ErrorCodeUtility::NOT_OWNER_OF_PIN,
                    'status_code' => '403'
                ], 403);
        }
        FileController::derefByString($media->file_ids);
        TagController::derefByString($media->tag_ids, $media->id, 'media');
        $pin_helper = PinHelper::where('pin_id', $media_id)->where('type', 'media')->first();
        $pin_helper->delete();
        PinOperationController::deletePinOperations('media', $media->id);
        PinOperationController::deletePinComments('media', $media->id);
        $media->delete();
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
        $medias = array();
        $total = 0;
        if($this->request->self_user_id == $user_id)
        {
            $medias = Medias::where('user_id', $user_id)
                            ->where('created_at','>=', $start_time)
                            ->where('created_at','<=', $end_time)
                            ->orderBy('created_at', 'desc')
                            ->skip(30 * ($page - 1))->take(30)->get();
            $total = Medias::where('user_id', $user_id)
                           ->where('created_at','>=', $start_time)
                           ->where('created_at','<=', $end_time)
                           ->count();
        }
        else
        {
            $medias = Medias::where('user_id', $user_id)
                            ->where('anonymous', false)
                            ->where('created_at','>=', $start_time)
                            ->where('created_at','<=', $end_time)
                            ->orderBy('created_at', 'desc')
                            ->skip(30 * ($page - 1))->take(30)->get();
            $total = Medias::where('user_id', $user_id)
                           ->where('anonymous', false)
                           ->where('created_at','>=', $start_time)
                           ->where('created_at','<=', $end_time)
                           ->count();
        }
        $total_pages = 0;
        if($total > 0)
        {
            $total_pages = intval(($total-1)/30)+1;
        }
        $info = array();
        foreach ($medias as $media)
        {
            $user_pin_operations = PinOperationController::getOperations('media', $media->id, $this->request->self_user_id);
            $info[] = array('media_id' => $media->id, 
                'user_id' => $media->user_id, 
                'file_ids' => explode(';', $media->file_ids), 
                'tag_ids' => explode(';', $media->tag_ids), 'description' => $media->description, 
                'geolocation'=>['latitude'=>$media->geolocation->getLat(), 'longitude'=>$media->geolocation->getLng()], 
                'liked_count' => $media->liked_count, 
                'saved_count' => $media->saved_count, 'comment_count' => $media->comment_count,
                'created_at' => $media->created_at->format('Y-m-d H:i:s'),
                'user_pin_operations' => $user_pin_operations);   
        }
        return $this->response->array($info)->header('page', $page)->header('total_pages', $total_pages);
    }

    private function createValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'geo_longitude' => 'required|numeric|between:-180,180',
            'geo_latitude' => 'required|numeric|between:-90,90',
            'file_ids' => 'required|regex:/^(\d+\;){0,5}\d+$/',
            'tag_ids' => 'filled|regex:/^(\d+\;){0,49}\d+$/',
            'description' => 'filled|string',
            'duration' => 'required|int|min:0',
            'interaction_radius' => 'filled|int|min:0',
            'anonymous' => 'filled|in:true,false'
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not create media.',$validator->errors());
        }
    }

    private function updateValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'geo_longitude' => 'filled|required_with:geo_latitude|required_without_all:file_ids,tag_ids,
                                description,duration,interaction_radius,anonymous|numeric|between:-180,180',
            'geo_latitude' => 'filled|required_with:geo_longitude|required_without_all:file_ids,tag_ids,
                                description,duration,interaction_radius,anonymous|numeric|between:-90,90',
            'file_ids' => 'filled|required_without_all:tag_ids,description,geo_longitude,geo_latitude,
                            duration,interaction_radius,anonymous|regex:/^(\d+\;){0,5}\d+$/',
            'tag_ids' => array('filled', 'required_without_all:file_ids,description,geo_longitude,geo_latitude,
                            duration,interaction_radius,anonymous', 'regex:/^(((\d+\;){0,49}\d+)|(null))$/'),
            'description' => 'filled|required_without_all:tag_ids,file_ids,geo_longitude,geo_latitude,
                              duration,interaction_radius,anonymous|string',
            'duration' => 'filled|required_without_all:tag_ids,file_ids,geo_longitude,geo_latitude,description,
                           interaction_radius,anonymous|int|min:0',
            'interaction_radius' => 'filled|required_without_all:tag_ids,file_ids,geo_longitude,geo_latitude,
                                     duration,description,anonymous|int|min:0',
            'anonymous' => 'filled|required_without_all:tag_ids,file_ids,geo_longitude,geo_latitude,
                            duration,description,interaction_radius|in:true,false'

        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not update media.',$validator->errors());
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