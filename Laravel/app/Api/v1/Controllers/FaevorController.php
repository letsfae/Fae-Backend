<?php

namespace App\Api\v1\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use App\Api\v1\Interfaces\PinInterface;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Validator;
use App\Faevors;
use App\Files;
use App\Tags;
use App\Users;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\Geometry;
use App\Api\V1\Controllers\TagController;
use App\Api\V1\Controllers\FileController;

class FaevorController extends Controller implements PinInterface {
    use Helpers;
    use PostgisTrait;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function create()
    {
        $this->createValidation($this->request);
        $faevor = new Faevors();
        if($this->request->has('file_ids'))
        {
            if(FileController::existsByString($this->request->file_ids))
            {
                $faevor->file_ids = $this->request->file_ids;
                FileController::refByString($this->request->file_ids);
            }
            else
            {
                return $this->errorNotFound();
            }
        }
        if(TagController::existsByString($this->request->tag_ids))
        {
            $faevor->tag_ids = $this->request->tag_ids;
            TagController::refByString($this->request->tag_ids);
        }
        else
        {

            return $this->errorNotFound();
        }
        $faevor->budget = $this->request->budget;
        if($this->request->has('bonus'))
        {
            $faevor->bonus = $this->request->bonus;
        }
        $faevor->name = $this->request->name;
        $faevor->description = $this->request->description;
        $faevor->due_time = $this->request->due_time;
        $faevor->expire_time = $this->request->expire_time;
        $faevor->geolocation = new Point($this->request->geo_latitude,$this->request->geo_longitude);
        $faevor->user_id = $this->request->self_user_id;
        $faevor->save();
        return $this->response->created(null, array('faevor_id' => $faevor->id));
    }

    public function update($faevor_id)
    {
        if(!is_numeric($faevor_id))
        {
            return $this->response->errorBadRequest();
        }
        $this->updateValidation($this->request);
        $faevor = Faevors::find($faevor_id);
        if(is_null($faevor))
        {
            return $this->response->errorNotFound();
        }
        if($this->request->has('file_ids'))
        { 
            if($this->request->file_ids == 'null')
            {
                FileController::updateRefByString($faevor->file_ids, $this->request->file_ids);
                $faevor->file_ids = null;
            }
            else if(FileController::existsByString($this->request->file_ids))
            {
                FileController::updateRefByString($faevor->file_ids, $this->request->file_ids);
                $faevor->file_ids = $this->request->file_ids;
            }
            else
            {
                return $this->errorNotFound();
            }
        }
        if($this->request->has('tag_ids'))
        {
            if(TagController::existsByString($this->request->tag_ids))
            {
                TagController::updateRefByString($faevor->tag_ids, $this->request->tag_ids);
                $faevor->tag_ids = $this->request->tag_ids;
            }
            else
            {
                return $this->errorNotFound();
            }
        }
        if($this->request->has('budget'))
        {
            $faevor->budget = $this->request->budget;
        }
        if($this->request->has('bonus'))
        {
            if($this->request->bonus == 'null')
            {
                $faevor->bonus = null;
            }
            else
            {
                $faevor->bonus = $this->request->bonus;
            }
        }
        if($this->request->has('name'))
        {
            $faevor->name = $this->request->name;
        }
        if($this->request->has('description'))
        {
            $faevor->description = $this->request->description;
        }
        if($this->request->has('due_time'))
        {
            $faevor->due_time = $this->request->due_time;
        }
        if($this->request->has('expire_time'))
        {
            $faevor->expire_time = $this->request->expire_time;
        }

        if($this->request->has('geo_latitude') && $this->request->has('geo_longitude'))
        {
            $faevor->geolocation = new Point($this->request->geo_latitude,$this->request->geo_longitude);
        }
        $faevor->save();
        return $this->response->created();

    }

    public function getOne($faevor_id)
    {
        if(!is_numeric($faevor_id))
        {
            return $this->response->errorBadRequest();
        }
        $faevor = Faevors::find($faevor_id);
        if(is_null($faevor))
        {
            return $this->response->errorNotFound();
        }
        $file_ids = is_null($faevor->file_ids) ? null : explode(';', $faevor->file_ids);
        $tag_ids = is_null($faevor->tag_ids) ? null : explode(';', $faevor->tag_ids);
        return $this->response->array(array('faevor_id' => $faevor->id, 'user_id' => $faevor->user_id, 'file_ids' => $file_ids, 
            'tag_ids' => $tag_ids, 'description' => $faevor->description, 'name' => $faevor->name, 'budget' => $faevor->budget, 
            'bonus' => $faevor->bonus, 'due_time' => $faevor->due_time, 'expire_time' => $faevor->expire_time, 
            'geolocation' => ['latitude' => $faevor->geolocation->getLat(), 'longitude' => $faevor->geolocation->getLng()], 
            'created_at' => $faevor->created_at->format('Y-m-d H:i:s')));
    }

    public function delete($faevor_id)
    {
        if(!is_numeric($faevor_id))
        {
            return $this->response->errorBadRequest();
        }
        $faevor = Faevors::find($faevor_id);
        if(is_null($faevor))
        {
            return $this->response->errorNotFound();
        }
        if($faevor->user_id != $this->request->self_user_id)
        {
            throw new AccessDeniedHttpException('You can not delete this faevor');
        }
        FileController::derefByString($faevor->file_ids);
        TagController::derefByString($faevor->tag_ids);
        $faevor->delete();
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
        $faevors = Faevors::where('user_id', $user_id)->where('created_at','>=', $start_time)->where('created_at','<=', $end_time)->orderBy('created_at', 'desc')->skip(30 * ($page - 1))->take(30)->get();
        $total_pages = intval($faevors->count() / 30) + 1;
        $info = array();
        foreach ($faevors as $faevor)
        {
            $file_ids = is_null($faevor->file_ids) ? null : explode(';', $faevor->file_ids);
            $tag_ids = is_null($faevor->tag_ids) ? null : explode(';', $faevor->tag_ids);
            $info[] = array('faevor_id' => $faevor->id, 'user_id' => $faevor->user_id, 'file_ids' => $file_ids, 'tag_ids' => $tag_ids, 'description' => $faevor->description, 'name' => $faevor->name, 'budget' => $faevor->budget, 'bonus' => $faevor->bonus, 'due_time' => $faevor->due_time, 'expire_time' => $faevor->expire_time, 'geolocation' => ['latitude' => $faevor->geolocation->getLat(), 'longitude' => $faevor->geolocation->getLng()], 'created_at' => $faevor->created_at->format('Y-m-d H:i:s'));
        }
        return $this->response->array($info)->header('page', $page)->header('total_pages', $total_pages);
    }

    private function createValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_ids' => 'filled|regex:/^(\d+\;){0,4}\d+$/',
            'tag_ids' => 'required|regex:/^(\d+\;){0,49}\d+$/',
            'budget' => 'required|integer',
            'bonus' => 'filled|string',
            'name' => 'required|string|max:100',
            'description' => 'required|string',
            'due_time' => 'required|date_format:Y-m-d H:i:s|before:tomorrow',
            'expire_time' => 'required|date_format:Y-m-d H:i:s|before:tomorrow', 
            'geo_longitude' => 'required|numeric|between:-180,180',
            'geo_latitude' => 'required|numeric|between:-90,90',
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not create faevor.',$validator->errors());
        }
    }

    private function updateValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_ids' => array('filled', 
                'required_without_all:tag_ids,budget,bonus,name,description,due_time,expire_time,geo_longitude,geo_latitude', 
                'regex:/^((((\d+\;){0,4}\d+))|(null))$/'),
            'tag_ids' => 'filled|required_without_all:file_ids,budget,bonus,name,description,due_time,expire_time,geo_longitude,geo_latitude|regex:/^(\d+\;){0,49}\d+$/',
            'budget' => 'filled|required_without_all:file_ids,tag_ids,bonus,name,description,due_time,expire_time,geo_longitude,geo_latitude|integer|min:0',
            'bonus' => array('filled', 
                'required_without_all:file_ids,tag_ids,budget,name,description,due_time,expire_time,geo_longitude,geo_latitude', 'regex:/^((\w{1,50})|(null))$/'),
            'name' => 'filled|required_without_all:file_ids,tag_ids,budget,bonus,description,due_time,expire_time,geo_longitude,geo_latitude|string|max:100',
            'description' => 'filled|required_without_all:file_ids,tag_ids,budget,bonus,name,due_time,expire_time,geo_longitude,geo_latitude|string',
            'due_time' => 'filled|required_without_all:file_ids,tag_ids,budget,bonus,name,description,expire_time,geo_longitude,geo_latitude|date_format:Y-m-d H:i:s|before:tomorrow',
            'expire_time' => 'filled|required_without_all:file_ids,tag_ids,budget,bonus,name,description,due_time,geo_longitude,geo_latitude|date_format:Y-m-d H:i:s|before:tomorrow',
            'geo_longitude' => 'filled|required_with:geo_latitude|required_without_all:file_ids,tag_ids,budget,bonus,name,description,due_time,expire_time|numeric|between:-180,180',
            'geo_latitude' => 'filled|required_with:geo_longitude|required_without_all:file_ids,tag_ids,budget,bonus,name,description,due_time,expire_time|numeric|between:-90,90',           
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not update faevor.',$validator->errors());
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
            throw new UpdateResourceFailedException('Could not get user faevors.',$validator->errors());
        }
    }
}