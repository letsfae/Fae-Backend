<?php

namespace App\Api\v1\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use App\Api\v1\Interfaces\PinInterface;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Validator;
use App\Medias;
use App\Files;
use App\Tags;
use App\Users;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\Geometry;
use App\Api\V1\Controllers\TagController;
use App\Api\V1\Controllers\FileController;

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
        if(!$this->fileIdsExists($this->request->file_ids) || !$this->tagIdsExists($this->request->tag_ids))
        {
            return $this->errorNotFound();
        }
        $this->filesRef($this->request->file_ids);
        $this->tagsRef($this->request->tag_ids);
        $media->file_ids = $this->request->file_ids;
        $media->tag_ids = $this->request->tag_ids;
        $media->description = $this->request->description;
        $media->geolocation = new Point($this->request->geo_latitude,$this->request->geo_longitude);
        $media->user_id = $this->request->self_user_id;
        $media->save();
        return $this->response->created(null, array('media_id' => $media->id));
    }

    public function update($media_id)
    {
        if(!is_numeric($media_id))
        {
            return $this->response->errorBadRequest();
        }
        $this->updateValidation($this->request);
        $media = Medias::find($media_id);
        if(is_null($media))
        {
            return $this->response->errorNotFound();
        }
        if($this->request->has('file_ids'))
        { 
            if($this->fileIdsExists($this->request->file_ids))
            {
                $this->filesRefUpdate($media->file_ids, $this->request->file_ids);
                $media->file_ids = $this->request->file_ids;
            }
            else
            {
                return $this->errorNotFound();
            }
        }
        if($this->request->has('tag_ids'))
        {
            if($this->tagIdsExists($this->request->tag_ids))
            {
                $this->tagsRefUpdate($media->tag_ids, $this->request->tag_ids);
                $media->tag_ids = $this->request->tag_ids;
            }
            else
            {
                return $this->errorNotFound();
            }
        }
        if($this->request->has('description'))
        {
            $media->description = $this->request->description;
        }
        if($this->request->has('geo_latitude') && $this->request->has('geo_longitude'))
        {
            $media->geolocation = new Point($this->request->geo_latitude,$this->request->geo_longitude);
        }
        $media->save();
        return $this->response->created();
    }

    public function getOne($media_id)
    {
        if(!is_numeric($media_id))
        {
            return $this->response->errorBadRequest();
        }
        $media = Medias::find($media_id);
        if(is_null($media))
        {
            return $this->response->errorNotFound();
        }
        $file_ids = is_null($media->file_ids) ? null : explode(';', $media->file_ids);
        $tag_ids = is_null($media->tag_ids) ? null : explode(';', $media->tag_ids);
        return $this->response->array(array('media_id' => $media->id, 'user_id' => $media->user_id, 'file_ids' => $file_ids, 
            'tag_ids' => $tag_ids, 'description' => $media->description, 'geolocation' => ['latitude' => $media->geolocation->getLat(), 
            'longitude' => $media->geolocation->getLng()], 'created_at' => $media->created_at->format('Y-m-d H:i:s')));
    }

    public function delete($media_id)
    {
        if(!is_numeric($media_id))
        {
            return $this->response->errorBadRequest();
        }
        $media = Medias::find($media_id);
        if(is_null($media))
        {
            return $this->response->errorNotFound();
        }
        if($media->user_id != $this->request->self_user_id)
        {
            throw new AccessDeniedHttpException('You can not delete this media');
        }
        $this->filesDeref($media->file_ids);
        $this->tagsDeref($media->tag_ids);
        $media->delete();
        return $this->response->noContent();
    }

    public function getFromUser($user_id)
    {
        if(!is_numeric($user_id))
        {
            return $this->response->errorBadRequest();
        }
        if (is_null(Users::find($user_id)))
        {
            return $this->response->errorNotFound();
        }
        $this->getFromUserValidation($this->request);
        $start_time = $this->request->has('start_time') ? $this->request->start_time : '1970-01-01 00:00:00';
        $end_time = $this->request->has('end_time') ? $this->request->end_time : date("Y-m-d H:i:s");
        $page =  $this->request->has('page') ? $this->request->page : 1;
        $medias = Medias::where('user_id', $user_id)->where('created_at','>=', $start_time)->where('created_at','<=', $end_time)->orderBy('created_at', 'desc')->skip(30 * ($page - 1))->take(30)->get();
        $total_pages = intval($medias->count() / 30) + 1;
        $info = array();
        foreach ($medias as $media)
        {
            $info[] = array('media_id' => $media->id, 'user_id' => $media->user_id, 'file_ids' => explode(';', $media->file_ids), 'tag_ids' => explode(';', $media->tag_ids), 'description' => $media->description, 'geolocation'=>['latitude'=>$media->geolocation->getLat(), 'longitude'=>$media->geolocation->getLng()], 'created_at' => $media->created_at->format('Y-m-d H:i:s'));   
        }
        return $this->response->array($info)->header('page', $page)->header('total_pages', $total_pages);
    }

    private function createValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'geo_longitude' => 'required|numeric|between:-180,180',
            'geo_latitude' => 'required|numeric|between:-90,90',
            'file_ids' => 'required|regex:/^(\d+\;){0,4}\d+$/',
            'tag_ids' => 'required|regex:/^(\d+\;){0,49}\d+$/',
            'description' => 'required|string',
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not create media.',$validator->errors());
        }
    }

    private function updateValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'geo_longitude' => 'filled|required_with:geo_latitude|required_without_all:file_ids,tag_ids,description|numeric|between:-180,180',
            'geo_latitude' => 'filled|required_with:geo_longitude|required_without_all:file_ids,tag_ids,description|numeric|between:-90,90',
            'file_ids' => 'filled|required_without_all:tag_ids,description,geo_longitude,geo_latitude|regex:/^(\d+\;){0,4}\d+$/',
            'tag_ids' => 'filled|required_without_all:file_ids,description,geo_longitude,geo_latitude|regex:/^(\d+\;){0,49}\d+$/',
            'description' => 'filled|required_without_all:tag_ids,file_ids,geo_longitude,geo_latitude|string',
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

    private function fileIdsExists($file_String)
    {
        return true;
        $file_ids = explode(';', $file_string);
        foreach($file_ids as $file_id)
        {
            if(!Files::where('id', $file_id)->exists())
            {
                return false;
            }
        }
        return true;
    }

    private function tagIdsExists($tag_string)
    {
        $tag_ids = explode(';', $tag_string);
        foreach($tag_ids as $tag_id)
        {
            if(!Tags::where('id', $tag_id)->exists())
            {
                return false;
            }
        }
        return true;
    }

    private function filesRef($file_ids_string)
    {
        $file_ids = explode(';', $file_ids_string);
        foreach ($file_ids as $file_id)
        {
            FileController::ref($file_id);
        }
    }

    private function filesDeref($file_ids_string)
    {
        $file_ids = explode(';', $file_ids_string);
        foreach ($file_ids as $file_id)
        {
            FileController::deref($file_id);
        }
    }

    private function filesRefUpdate($old_file_ids_string, $new_file_ids_string)
    {
        if($old_file_ids_string != null)
        {
            $old_file_ids = explode(';', $old_file_ids_string);
            foreach ($old_file_ids as $file_id)
            {
                FileController::deref($file_id);
            }
        }
        if($new_file_ids_string != 'null')
        {
            $new_file_ids = explode(';', $new_file_ids_string);
            foreach ($new_file_ids as $file_id)
            {
                FileController::ref($file_id);
            }
        }
    }

    private function tagsRef($tag_ids_string)
    {
        $tag_ids = explode(';', $tag_ids_string);
        foreach ($tag_ids as $tag_id)
        {
            TagController::ref($tag_id);
        }
    }

    private function tagsDeref($tag_ids_string)
    {
        $tag_ids = explode(';', $tag_ids_string);
        foreach ($tag_ids as $tag_id)
        {
            TagController::deref($tag_id);
        }
    }

    private function tagsRefUpdate($old_tag_ids_string, $new_tag_ids_string)
    {
        $old_tag_ids = explode(';', $old_tag_ids_string);
        $new_tag_ids = explode(';', $new_tag_ids_string);
        foreach ($old_tag_ids as $tag_id)
        {
            TagController::deref($tag_id);
        }
        foreach ($new_tag_ids as $tag_id)
        {
            TagController::ref($tag_id);
        }
    }
}