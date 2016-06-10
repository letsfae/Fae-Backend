<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Exception\UpdateResourceFailedException;
use App\Sessions;
use Validator;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;

class MapController extends Controller
{
    use Helpers;
    use PostgisTrait;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getMap()
    {
    }

    private function getMapValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'geo_longitude' => 'required|numeric|between:-180,180',
            'geo_latitude' => 'required|numeric|between:-90,90',
            'radius' => 'integer|min:0',
            'type' => 'filled|string',
            'max_count' => 'filled|integer|between:1,100',
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not get map.',$validator->errors());
        }
    }

    public function updateUserLocation()
    {        
        $this->locationValidation($this->request);
        $session = Sessions::find($this->request->self_session_id);
        if(is_null($session))
        {
            return $this->response->errorNotFound();
        }
        if($session->active)
        {
            $session->location = new Point($this->request->geo_latitude,$this->request->geo_longitude);
            $session->save();
            return $this->response->created();
        }
        else
        {
            throw new UpdateResourceFailedException('current user is not active');
        }
    }

    private function locationValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'geo_longitude' => 'required|numeric|between:-180,180',
            'geo_latitude' => 'required|numeric|between:-90,90',
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not update user location.',$validator->errors());
        }
    }

    public function setActive()
    {
        $this->setUserActive($this->request->self_session_id);
        return $this->response->created();
    }

    public static function setUserActive($session_id)
    {
        $session = Sessions::find($session_id);
        $session->active = true;
        $session->save();
        $sessions = Sessions::where('user_id',$session->user_id)->where('id','!=',$session_id)->get();
        foreach($sessions as $session)
        {
            $session->active = false;
            $session->save();
        }
    }

    public function getActive() 
    {
        $session = Sessions::find($this->request->self_session_id);
        $session_active = Sessions::where('user_id',$this->request->self_user_id)->where('active',true)->first();
        if(!is_null($session) && !is_null($session_active))
        {
            $info = array('is_active' => $session->active, 'active_device_id' => $session_active->device_id);
            return $this->response->array($info);
        }
        return $this->response->errorNotFound();
    }
}
