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
use App\Users;
use App\Api\v1\Controllers\PinOperationController;
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
        $in_duration_str = $this->request->has('in_duration') ? $this->request->in_duration : 'false';
        $in_duration = $in_duration_str == 'true' ? true : false;
        $user_updated_in = $this->request->has('user_updated_in') ? $this->request->user_updated_in : 0;
        $info = array();
        $type = array();
        if($this->request->type == 'user')
        {
            $sessions = array();
            if($user_updated_in == 0)
            {
                $sessions = DB::select("SELECT user_id,location,created_at
                                        FROM sessions
                                        WHERE st_dwithin(location,ST_SetSRID(ST_Point(:longitude, :latitude),4326),:radius,true)
                                        ORDER BY ST_Distance(location, ST_SetSRID(ST_Point(:longitude, :latitude),4326)), user_id
                                        LIMIT :max_count;", 
                                        array('longitude' => $longitude, 'latitude' => $latitude,
                                              'radius' => $radius, 'max_count' => $max_count));
            }
            else
            {
                $sessions = DB::select("SELECT user_id,location,created_at
                                        FROM sessions
                                        WHERE st_dwithin(location,ST_SetSRID(ST_Point(:longitude, :latitude),4326),:radius,true)
                                        AND CURRENT_TIMESTAMP - location_updated_at < ".strval($user_updated_in)." * INTERVAL '1 min' 
                                        ORDER BY ST_Distance(location, ST_SetSRID(ST_Point(:longitude, :latitude),4326)), user_id
                                        LIMIT :max_count;", 
                                        array('longitude' => $longitude, 'latitude' => $latitude,
                                              'radius' => $radius, 'max_count' => $max_count));
            }
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
                    $distance = mt_rand(300,600);
                    $degree = mt_rand(0,360);
                    $locations_original = DB::select("select ST_AsText(ST_Project(ST_SetSRID(ST_Point(:longitude,:latitude),4326),:distance, radians(:degree)))", 
                        array('longitude' => $location->getLng(),'latitude'=>$location->getLat(),'distance'=>$distance,'degree'=>$degree));
                        $locations[] = Point::fromWKT($locations_original[0]->st_astext);
                }
                $user = Users::find($session->user_id);
                if(is_null($user))
                {
                    continue;
                }
                $info[] = ['type'=>'user', 'user_id' => $session->user_id, 
                           'mini_avatar' => $user->mini_avatar, 'geolocation'=>[
                           ['latitude'=>$locations[0]->getLat(),'longitude'=>$locations[0]->getLng()],
                           ['latitude'=>$locations[1]->getLat(),'longitude'=>$locations[1]->getLng()],
                           ['latitude'=>$locations[2]->getLat(),'longitude'=>$locations[2]->getLng()],
                           ['latitude'=>$locations[3]->getLat(),'longitude'=>$locations[3]->getLng()],
                           ['latitude'=>$locations[4]->getLat(),'longitude'=>$locations[4]->getLng()]],
                           'created_at'=>$session->created_at];
            }
        }
        else
        {
            $types = explode(',', $this->request->type);
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

            $sql_select = "SELECT p1.pin_id, p1.type FROM pin_helper p1";
            if($this->request->has('is_saved') || $this->request->has('is_liked') || $this->request->has('is_read'))
            {
                $sql_select .= ", pin_operations p2";
            }
            $sql_select .= "\nWHERE st_dwithin(geolocation,ST_SetSRID(ST_Point(:longitude, :latitude),4326),:radius,true)\n";
            if($in_duration == true)
            {
                $sql_select .= "AND CURRENT_TIMESTAMP - p1.created_at < duration * INTERVAL '1 min'\n";
            }
            $sql_select .= "AND p1.type IN (".$type_string.")\n";
            if($this->request->has('is_saved') || $this->request->has('is_liked') || $this->request->has('is_read'))
            {
                if(!$this->request->has('is_read') || $this->request->is_read == 'true')
                {
                    $sql_select .= "AND p1.pin_id = p2.pin_id
                                    AND p1.type = p2.type
                                    AND p2.user_id = :user_id\n";
                    if($this->request->has('is_saved')) 
                    {
                        $sql_select .= "AND p2.saved = ".$this->request->is_saved."\n";
                    }
                    if($this->request->has('is_liked'))
                    {
                        $sql_select .= "AND p2.liked = ".$this->request->is_liked."\n";
                    }
                }
                else
                {
                    $sql_select .= "AND NOT EXISTS
                                    (SELECT * FROM pin_operations p3 
                                    WHERE p1.pin_id = p3.pin_id 
                                    AND p1.type = p3.type  
                                    AND p3.user_id = :user_id)\n";
                }   
            }
            $sql_select .= "ORDER BY p1.created_at
                            LIMIT :max_count;";
            if($this->request->has('is_saved') || $this->request->has('is_liked') || $this->request->has('is_read'))
            {
                $pin_helpers = DB::select($sql_select, array('longitude' => $longitude, 'latitude' => $latitude,
                                                             'radius' => $radius, 'max_count' => $max_count, 
                                                             'user_id' => $this->request->self_user_id));
            }
            else
            {
                $pin_helpers = DB::select($sql_select, array('longitude' => $longitude, 'latitude' => $latitude,
                                                             'radius' => $radius, 'max_count' => $max_count));
            }
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
                               'longitude' => $comment->geolocation->getLng()],
                               'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                               'user_pin_operations' => PinOperationController::getOperations('comment', $pin_helper->pin_id, $this->request->self_user_id)];
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
                               'longitude' => $media->geolocation->getLng()], 
                               'created_at' => $media->created_at->format('Y-m-d H:i:s'),
                               'user_pin_operations' => PinOperationController::getOperations('media', $pin_helper->pin_id, $this->request->self_user_id)];
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
                               'created_at' => $chat_room->created_at->format('Y-m-d H:i:s'),];
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
            'in_duration' => 'filled|in:true,false',
            'user_updated_in' => 'filled|integer|min:1',
            'is_saved' => 'filled|in:true,false',
            'is_liked' => 'filled|in:true,false',
            'is_read' => 'filled|in:true,false',
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
            $session->location = new Point($this->request->geo_latitude, $this->request->geo_longitude);
            $session->updateLocationTimestamp();
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