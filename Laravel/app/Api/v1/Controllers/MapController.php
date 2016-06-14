<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Exception\UpdateResourceFailedException;
use App\Sessions;
use Validator;
use DB;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\Geometry;

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
        $this->getMapValidation($this->request);
        $location = new Point($this->request->geo_latitude,$this->request->geo_longitude);
        $longitude = -90;
        $latitude = 5;
        $radius = $this->request->has('radius') ? $this->request->radius:200;
        $max_count = $this->request->has('max_count') ? $this->request->max_count:30;
        $type = array();
        if($this->request->has('type'))
        {
            $types = explode(',',$this->request->type);
            foreach($types as $t)
            {
                $t = trim($t);
                switch($t)
                {
                    case 'user':
                        $type[] = 'user';
                        continue;
                    case 'comment':
                        $type[] = 'comment';
                        continue;
                    default:
                        return $this->response->errorNotFound();
                }
            }
        }
        else
        {
            array_push($type, 'user','comment');
        }
        $info = array();
        foreach($type as $t)
        {
            switch($t)
            {
                case 'user':
                    $sessions = DB::select("select * from sessions s where st_dwithin(s.location,ST_SetSRID(ST_Point(:longitude, :latitude),4326),:radius,true) limit :max_count", array('longitude' => $longitude, 'latitude' => $latitude, 'radius' => $radius, 'max_count' => $max_count));
                    foreach($sessions as $session)
                    {
                        $location = Geometry::fromWKB($session->location); 
                        $info[] = ['type'=>'user','geolocation'=>['latitude'=>$location->getLat(),
                        'longitude'=>$location->getLng()],'created_at'=>$session->created_at];
                    }
                    continue;
                case 'comment':
                    $comments = DB::select("select * from comments c where st_dwithin(c.geolocation,ST_SetSRID(ST_Point(:longitude, :latitude),4326),:radius,true) limit :max_count", array('longitude' => $longitude, 'latitude'=> $latitude, 'radius' => $radius, 'max_count' => $max_count));
                    foreach($comments as $comment)
                    {
                        $location = Geometry::fromWKB($comment->geolocation);
                        $info[] = ['type'=>'comment','user_id' => $comment->user_id,'content' => $comment->content ,'geolocation'=>['latitude'=>$location->getLat(), 'longitude'=>$location->getLng()],'created_at'=>$comment->created_at];
                    }
                    continue;
                default:
                    return $this->response->errorNotFound();
            }
        }
        return $this->response->array($info);
    }

    private function getMapValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'geo_longitude' => 'required|numeric|between:-180,180',
            'geo_latitude' => 'required|numeric|between:-90,90',
            'radius' => 'filled|integer|min:0',
            'type' => 'filled|string',
            'max_count' => 'filled|integer|between:0,100',
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
