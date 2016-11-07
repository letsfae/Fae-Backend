<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Exception\UpdateResourceFailedException;
use App\Sessions;
use App\PinHelper;
use App\Comments;
use App\ChatRooms;
use App\Medias;
use App\User_exts;
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
        $longitude = $this->request->geo_longitude;
        $latitude = $this->request->geo_latitude;
        $radius = $this->request->has('radius') ? $this->request->radius:200;
        $max_count = $this->request->has('max_count') ? $this->request->max_count:30;
        $in_duration_str = $this->request->has('in_duration') ? $this->request->in_duration:'false';
        $in_duration = $in_duration_str == 'true' ? true:false;
        $info = array();
        $type = array();
        if($this->request->type == 'user')
        {
            $sessions = DB::select("SELECT user_id,location,created_at
                                    FROM sessions
                                    WHERE st_dwithin(location,ST_SetSRID(ST_Point(:longitude, :latitude),4326),:radius,true)
                                    ORDER BY ST_Distance(location, ST_SetSRID(ST_Point(:longitude, :latitude),4326)), user_id
                                    LIMIT :max_count;", 
                                    array('longitude' => $longitude, 'latitude' => $latitude,
                                          'radius' => $radius, 'max_count' => $max_count));   
            // $distance = DB::select("SELECT ST_Distance_Spheroid(ST_SetSRID(ST_Point(55, 56.1),4326), ST_SetSRID(ST_Point(56, 56),4326), 'SPHEROID[\"WGS 84\",6378137,298.257223563]')");
            // echo $distance[0]->st_distance_spheroid; exit();                 
            foreach($sessions as $session)
            {
                $user_exts = User_exts::find($session->user_id);
                if(is_null($user_exts) || $user_exts->status == 5)
                {
                    continue;
                }
                $location = Geometry::fromWKB($session->location);
                $locations = array();
                for($i = 0; $i < 5; $i++)
                {
                    $distance = mt_rand(1,200);
                    $degree = mt_rand(0,360);
                    $locations_original = DB::select("select ST_AsText(ST_Project(ST_SetSRID(ST_Point(:longitude,:latitude),4326),:distance, radians(:degree)))", 
                        array('longitude' => $location->getLng(),'latitude'=>$location->getLat(),'distance'=>$distance,'degree'=>$degree));
                        $locations[] = Point::fromWKT($locations_original[0]->st_astext);
                } 
                $info[] = ['type'=>'user','user_id' => $session->user_id,'geolocation'=>[['latitude'=>$locations[0]->getLat(),
                            'longitude'=>$locations[0]->getLng()],['latitude'=>$locations[1]->getLat(),
                            'longitude'=>$locations[1]->getLng()],['latitude'=>$locations[2]->getLat(),
                            'longitude'=>$locations[2]->getLng()],['latitude'=>$locations[3]->getLat(),
                            'longitude'=>$locations[3]->getLng()],['latitude'=>$locations[4]->getLat(),
                            'longitude'=>$locations[4]->getLng()]],'created_at'=>$session->created_at];
            }
        }
        else
        {
            $types = explode(',',$this->request->type);
            foreach($types as $t)
            {
                $t = trim($t);
                switch($t)
                {
                    case 'user':
                        throw new UpdateResourceFailedException('Could not get map. Wrong types.');
                        break;
                    case 'comment':
                        $type[] = "'comment'";
                        break;
                    case 'media':
                        $type[] = "'media'";
                        break;
                    case 'chat_room':
                        $type[] = "'chat_room'";
                        break;
                    default:
                        throw new UpdateResourceFailedException('Could not get map. Wrong types.');
                }
            }
            
            $type_string = implode(",", $type);

            $sql_select = "";
            if($in_duration == true)
            {
                $sql_select = "SELECT pin_id, type
                               FROM pin_helper 
                               WHERE st_dwithin(geolocation,ST_SetSRID(ST_Point(:longitude, :latitude),4326),:radius,true)
                               AND CURRENT_TIMESTAMP - created_at < duration * INTERVAL '1 min' 
                               AND type IN (".$type_string.")
                               ORDER BY created_at 
                               LIMIT :max_count;";
            }
            else
            {
                $sql_select = "SELECT pin_id, type
                               FROM pin_helper 
                               WHERE st_dwithin(geolocation,ST_SetSRID(ST_Point(:longitude, :latitude),4326),:radius,true)
                               AND type IN (".$type_string.")
                               ORDER BY created_at 
                               LIMIT :max_count;";
            }
            $pin_helpers = DB::select($sql_select, array('longitude' => $longitude, 'latitude' => $latitude,
                                                         'radius' => $radius, 'max_count' => $max_count));
            foreach ($pin_helpers as $pin_helper)
            {
                if($pin_helper->type == 'comment')
                {
                    $comment = Comments::find($pin_helper->pin_id);
                    if(is_null($comment))
                    {
                        continue;
                    }
                    $info[] = ['type'=>'comment','comment_id' => $comment->id,'user_id' => $comment->user_id,
                               'content' => $comment->content ,'geolocation'=>['latitude'=>$comment->geolocation->getLat(), 
                               'longitude'=>$comment->geolocation->getLng()],'created_at'=>$comment->created_at->format('Y-m-d H:i:s')];
                }
                else if($pin_helper->type == 'media')
                {
                    $media = Medias::find($pin_helper->pin_id);
                    if(is_null($media))
                    {
                        continue;
                    }
                    $info[] = ['type'=>'media', 'media_id' => $media->id, 'user_id' => $media->user_id, 
                               'file_ids' => explode(';', $media->file_ids), 'tag_ids' => explode(';', $media->tag_ids), 
                               'description' => $media->description, 'geolocation'=>['latitude' => $media->geolocation->getLat(), 
                               'longitude' => $media->geolocation->getLng()], 'created_at' => $media->created_at->format('Y-m-d H:i:s')];
                }
                else if($pin_helper->type == 'chat_room')
                {
                    $chat_room = ChatRooms::find($pin_helper->pin_id);
                    if(is_null($chat_room))
                    {
                        continue;
                    }
                    $info[] = ['type' => 'chat_room', 'chat_room_id' => $chat_room->id, 'title' => $chat_room->title, 
                               'user_id' => $chat_room->user_id, 'geolocation' => ['latitude' => $chat_room->geolocation->getLat(), 
                               'longitude' => $chat_room->geolocation->getLng()], 'last_message' => $chat_room->last_message, 
                               'last_message_sender_id' => $chat_room->last_message_sender_id,
                               'last_message_type' => $chat_room->last_message_type, 
                               'last_message_timestamp' => $chat_room->last_message_timestamp,
                               'created_at' => $chat_room->created_at->format('Y-m-d H:i:s')];
                }
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
            'type' => 'required|string',
            'max_count' => 'filled|integer|between:0,100',
            'in_duration' => 'filled|in:true,false'
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
        if($session->is_mobile)
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
}