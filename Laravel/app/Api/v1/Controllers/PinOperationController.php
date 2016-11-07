<?php
namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Exception\StoreResourceFailedException;
use Validator;
use App\Pin_operations;
use App\Comments;
use App\Medias;
use App\Pin_comments;
use App\Sessions;
use DB;

class PinOperationController extends Controller {
    use Helpers;
    const UNLIKE = 1;
    const UNCOMMENT = 2;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function save($type, $pin_id) {
        if(!is_numeric($pin_id) || ($type != 'media' && $type != 'comment'))
        {
            return $this->response->errorBadRequest();
        }
        $obj_pin_operation = $this->readOperation($type, $pin_id);
        if(is_null($obj_pin_operation))
        {
            return $this->response->errorNotFound();
        }
        $obj_pin_operation->saved = true;
        $obj_pin_operation->updateSavedTimestamp();
        $obj_pin_operation->save();
        return $this->response->created();
    }

    public function unsave($type, $pin_id) {
        if(!is_numeric($pin_id) || ($type != 'media' && $type != 'comment'))
        {
            return $this->response->errorBadRequest();
        }
        $obj_pin_operation = $this->readOperation($type, $pin_id);
        if(is_null($obj_pin_operation))
        {
            throw new StoreResourceFailedException('Bad request, No such pin exist!');
        }
        if($obj_pin_operation->saved == false)
        {
            throw new StoreResourceFailedException('The user has not saved this pin');
        }
        $obj_pin_operation->saved = false;
        $obj_pin_operation->updateSavedTimestamp();
        $obj_pin_operation->save();
        return $this->response->noContent();
    }

    public static function getOperations($type, $pin_id, $user_id)
    {
        if(!is_numeric($pin_id) || !is_numeric($user_id))
        {
            return $this->response->errorBadRequest();
        }
        $pin_operation = Pin_operations::where('pin_id', $pin_id)->where('user_id', $user_id)->
                         where('type', $type)->first();
        if($pin_operation == null)
        {
            return array('is_liked' => false, 'liked_timestamp' => null, 
                         'is_saved' => false, 'saved_timestamp' => null,
                         'is_read'  => false, 'read_timestamp'  => null);  
        }
        return array('is_liked' => $pin_operation->liked, 'liked_timestamp' => $pin_operation->liked_timestamp, 
                     'is_saved' => $pin_operation->saved, 'saved_timestamp' => $pin_operation->saved_timestamp,
                     'is_read'  => true, 'read_timestamp'  => $pin_operation->created_at->format('Y-m-d H:i:s'));
    }

    public function read($type, $pin_id)
    {
        if(!is_numeric($pin_id) || ($type != 'media' && $type != 'comment'))
        {
            return $this->response->errorBadRequest();
        }
        $obj_pin_operation = $this->readOperation($type, $pin_id);
        if(is_null($obj_pin_operation))
        {
            throw new StoreResourceFailedException('Bad request, No such pin exist!');
        }
        return $this->response->created();
    }

    //internal invocation
    public function readOperation($type, $pin_id)
    {
        $obj_pin_operation = Pin_operations::where('pin_id', $pin_id)
                                           ->where('user_id', $this->request->self_user_id)
                                           ->where('type', $type)->first();
        if(is_null($obj_pin_operation))
        {
            $obj = null;
            if($type == 'media')
            {
                $obj = Medias::find($pin_id);
            }
            else if($type == 'comment')
            {
                $obj = Comments::find($pin_id);
            }
            if (is_null($obj))
            {
                return null;
            }
            else
            {
                $newobj_pin_operation = new Pin_operations;
                $newobj_pin_operation->user_id = $this->request->self_user_id;
                $newobj_pin_operation->pin_id = $pin_id;
                $newobj_pin_operation->type = $type;
                $newobj_pin_operation->save();
                return $newobj_pin_operation;
            }
        }
        else
        {
            return $obj_pin_operation;
        }
    }

    public function like($type, $pin_id)
    {
        if(!is_numeric($pin_id) || ($type != 'media' && $type != 'comment'))
        {
            return $this->response->errorBadRequest();
        }
        $obj_pin_operation = $this->readOperation($type, $pin_id);
        if(is_null($obj_pin_operation))
        {
            throw new StoreResourceFailedException('Bad request, No such pin exist!');
        }
        if($obj_pin_operation->interacted == false)
        {
            $inDistance = $this->checkDistance($this->request->self_user_id, $this->request->self_session_id, $type, $pin_id);
            if($inDistance === false)
            {
                return $this->response->errorBadRequest('too far away');
            }
            if(is_null($inDistance))
            {
                if($inDistance == null)
                return $this->response->errorNotFound();
            }
        }
        $obj_pin_operation->liked = true;
        $obj_pin_operation->interacted = true;
        $obj_pin_operation->updateLikeTimestamp();
        $obj_pin_operation->save();
        return $this->response->created();
    }

    public function unlike($type, $pin_id)
    {
        if(!is_numeric($pin_id) || ($type != 'media' && $type != 'comment'))
        {
            return $this->response->errorBadRequest();
        }
        $obj_pin_operation = $this->readOperation($type, $pin_id);
        if(is_null($obj_pin_operation))
        {
            throw new StoreResourceFailedException('Bad request, No such pin exist!');
        }
        if($obj_pin_operation->liked == false) 
        {
            throw new StoreResourceFailedException('Bad request, never liked such pin!');
        }
        $obj_pin_operation->liked = false;
        $obj_pin_operation->updateLikeTimestamp();
        if($this->checkInteracted($type, $pin_id, self::UNLIKE) == false)
        {
            $obj_pin_operation->interacted = false;
        }
        $obj_pin_operation->save();
        return $this->response->noContent();
    }

    public function comment($type, $pin_id)
    {
        if(!is_numeric($pin_id) || ($type != 'media' && $type != 'comment'))
        {
            return $this->response->errorBadRequest();
        }
        $validator = Validator::make($this->request->all(), [
            'content' => 'required|string|max:100'
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not comment.', $validator->errors());
        }
        $obj_pin_operation = $this->readOperation($type, $pin_id);
        if(is_null($obj_pin_operation))
        {
            throw new StoreResourceFailedException('Bad request, No such pin exist!');
        }
        if($obj_pin_operation->interacted == false)
        {
            $inDistance = $this->checkDistance($this->request->self_user_id, $this->request->self_session_id, $type, $pin_id);
            if($inDistance === false)
            {
                return $this->response->errorBadRequest('too far away');
            }
            if($inDistance == null)
            {
                return $this->response->errorNotFound();
            }
        }
        $obj_pin_operation->interacted = true;
        $obj_pin_operation->save();
        $newobj_pin_comment = new Pin_comments;
        $newobj_pin_comment->user_id = $this->request->self_user_id;
        $newobj_pin_comment->pin_id = $pin_id;
        $newobj_pin_comment->type = $type;
        $newobj_pin_comment->content = $this->request->content;
        $newobj_pin_comment->save();
        $content = array('pin_comment_id' => $newobj_pin_comment->id);
        return $this->response->created(null, $content);
    }

    public function uncomment($pin_comment_id)
    {
        if(!is_numeric($pin_comment_id))
        {
            return $this->response->errorBadRequest();
        }
        $obj_pin_comment = Pin_comments::find($pin_comment_id);
        if (is_null($obj_pin_comment))
        {
            throw new StoreResourceFailedException('Bad request, no such comment exists');
        }
        if($obj_pin_comment->user_id != $this->request->self_user_id)
        {
            throw new StoreResourceFailedException('You can not delete this comment');
        }
        $type = $obj_pin_comment->type;
        $pin_id = $obj_pin_comment->pin_id;
        $op = self::UNCOMMENT;
        $obj_pin_comment->delete();

        $obj_pin_operation = $this->readOperation($type, $pin_id);
        if(is_null($obj_pin_operation))
        {
            return $this->request->errorNotFound();
        }
        $obj_pin_operation->interacted = $this->checkInteracted($type, $pin_id, $op);
        $obj_pin_operation->save();
        return $this->response->noContent();
    }

    public function getPinAttribute($type, $pin_id) {
        if(!is_numeric($pin_id) || ($type != 'media' && $type != 'comment'))
        {
            return $this->response->errorBadRequest();
        }
        if($type == 'media'){
            $obj_media = Medias::where('id', $pin_id)->first();
            if ($obj_media == null) {
                throw new StoreResourceFailedException('Bad request, No such pin exist!');
            } 
        }else if ($type == 'comment'){
            $obj_comment = Comments::where('id', $pin_id)->first();
            if ($obj_comment == null) {
                throw new StoreResourceFailedException('Bad request, No such pin exist!');
            }
        }
        
        $num_liked_pins = Pin_operations::where('pin_id', $pin_id)->where('type', $type)->where('liked', 'TRUE')->count();
        //$num_liked_pins = count($liked_pins);
        $num_saved_pins = Pin_operations::where('pin_id', $pin_id)->where('type', $type)->where('saved', 'TRUE')->count();
        //$num_saved_pins = count($saved_pins);
        $num_commented_pins = Pin_comments::where('pin_id', $pin_id)->where('type', $type)->count();
        //$num_commented_pins = count($commented_pins);
        
        $content = array(   'type' => $type,
                            'pin_id'=> intval($pin_id),
                            'likes' => $num_liked_pins, 
                            'saves' => $num_saved_pins, 
                            'comments' => $num_commented_pins);
        return $this->response->created(null, $content);
    }

    public function getPinCommentList($type, $pin_id) {
        if(!is_numeric($pin_id) || ($type != 'media' && $type != 'comment'))
        {
            return $this->response->errorBadRequest();
        }
        if($type == 'media'){
            $obj_media = Medias::where('id', $pin_id)->first();
            if ($obj_media == null) {
                throw new StoreResourceFailedException('Bad request, No such pin exist!');
            } 
        }else if ($type == 'comment'){
            $obj_comment = Comments::where('id', $pin_id)->first();
            if ($obj_comment == null) {
                throw new StoreResourceFailedException('Bad request, No such pin exist!');
            }
        }
        
        $start_time = $this->request->has('start_time') ? $this->request->start_time : '1970-01-01 00:00:00';
        $end_time = $this->request->has('end_time') ? $this->request->end_time : date("Y-m-d H:i:s");
        $page =  $this->request->has('page') ? $this->request->page : 1;
        
        $total = Pin_comments::where('pin_id', $pin_id)
                                ->where('type', $type)
                                ->where('created_at','>=', $start_time)
                                ->where('created_at','<=', $end_time)
                                ->count();
        $total_pages = 0;
        if($total>0){
            $total_pages = intval(( $total - 1 )/30) +1;
        }
        
        $commented_pin_list = Pin_comments::where('pin_id', $pin_id)
                                          ->where('type', $type)
                                          ->where('created_at','>=', $start_time)
                                          ->where('created_at','<=', $end_time)
                                          ->orderBy('created_at', 'desc')
                                          ->skip(30 * ($page - 1))->take(30)->get();
        
        $info = array();
        foreach($commented_pin_list as $commented_pin)
        {
            $info[] = array('pin_comment_id' => $commented_pin->id,
                            'user_id' => $commented_pin->user_id,
                            'content' => $commented_pin->content,
                            'created_at'=>$commented_pin->created_at->format('Y-m-d H:i:s'));
        }
        return $this->response->array($info)->header('page', $page)->header('total_pages', $total_pages);
    }

    public function getUserPinList($user_id) {
        if(!is_numeric($user_id))
        {
            return $this->response->errorBadRequest();
        }
        $start_time = $this->request->has('start_time') ? $this->request->start_time : '1970-01-01 00:00:00';
        $end_time = $this->request->has('end_time') ? $this->request->end_time : date("Y-m-d H:i:s");
        $page =  $this->request->has('page') ? $this->request->page : 1;
        
        $total = Pin_comments::where('user_id', $user_id)
                                ->where('created_at','>=', $start_time)
                                ->where('created_at','<=', $end_time)
                                ->count();
        $total_pages = 0;
        if($total>0){
            $total_pages = intval(( $total - 1 )/30) +1;
        }
        
        $commented_pin_list = Pin_comments::where('user_id', $user_id)
                                          ->where('created_at','>=', $start_time)
                                          ->where('created_at','<=', $end_time)
                                          ->orderBy('created_at', 'desc')
                                          ->skip(30 * ($page - 1))->take(30)->get();
        
        $info = array();
        foreach($commented_pin_list as $commented_pin)
        {
            $info[] = array('pin_id' => $commented_pin->pin_id,
                            'type' => $commented_pin->type,
                            'content' => $commented_pin->content);
        }
        return $this->response->array($info)->header('page', $page)->header('total_pages', $total_pages);
    }

    public function getSelfPinList() {
        return $this->getUserPinList($this->request->self_user_id);
    }

    public function getSavedPinList() {
        $user_id = $this->request->self_user_id;
        $start_time = $this->request->has('start_time') ? $this->request->start_time : '1970-01-01 00:00:00';
        $end_time = $this->request->has('end_time') ? $this->request->end_time : date("Y-m-d H:i:s");
        $page =  $this->request->has('page') ? $this->request->page : 1;
        
        $total = Pin_operations::where('user_id', $user_id)
                               ->where('saved', 'TRUE')
                               ->where('saved_timestamp','>=', $start_time)
                               ->where('saved_timestamp','<=', $end_time)
                               ->count();
        $total_pages = 0;
        if($total>0){
            $total_pages = intval(( $total - 1 )/30) +1;
        }
        
        $saved_pin_list = Pin_operations::where('user_id', $user_id)
                                        ->where('saved_timestamp','>=', $start_time)
                                        ->where('saved_timestamp','<=', $end_time)
                                        ->orderBy('saved_timestamp', 'desc')
                                        ->skip(30 * ($page - 1))->take(30)->get();
        $info = array();
        foreach($saved_pin_list as $saved_pin)
        {
            $info[] = array('pin_id' => $saved_pin->pin_id,
                            'type' => $saved_pin->type,
                            'created_at' => $saved_pin->saved_timestamp);
        }
        return $this->response->array($info)->header('page', $page)->header('total_pages', $total_pages);
    }

    public function vote()
    {

    }

    public function cancelVote()
    {
        
    }

    public function checkInteracted($type, $pin_id, $op)
    {
        $hasComments = Pin_comments::where('pin_id', $pin_id)->where('type', $type)->exists();
        if($hasComments == true)
        {
            return true;
        }
        else
        {
            if($op == self::UNLIKE)
            {
                return false;
            }
            else if($op == self::UNCOMMENT)
            {
                $obj_pin_operation = Pin_operations::where('pin_id', $pin_id)
                                                   ->where('user_id', $this->request->self_user_id)
                                                   ->where('type', $type)->first();
                if(is_null($obj_pin_operation) || $obj_pin_operation->liked == false)
                {
                    return false;
                }
                else
                {
                    return true;
                }
            }
            else
            {
                return true;
            }
        }
    }
    public static function getObj($type, $pin_id)
    {
        $obj = null;
        if($type == 'media')
        {
            $obj = Medias::find($pin_id);
        }
        else if ($type == 'comment')
        {
            $obj = Comments::find($pin_id);
        }
        return $obj;
    }
    public static function checkDistance($user_id, $session_id, $type, $pin_id)
    {
        $obj = self::getObj($type, $pin_id);
        if($user_id != $obj->user_id)
        {
            $session = Sessions::find($session_id);
            if(is_null($session) || !$session->is_mobile || $session->location == null)
            {
                return null;
            }
            $distance = DB::select("SELECT ST_Distance_Spheroid(ST_SetSRID(ST_Point(:longitude1, :latitude1),4326), 
                ST_SetSRID(ST_Point(:longitude2, :latitude2),4326), 'SPHEROID[\"WGS 84\",6378137,298.257223563]')",
                array('longitude1' => $session->location->getLng(), 'latitude1' => $session->location->getLat(),
                      'longitude2' => $obj->geolocation->getLng(), 'latitude2' => $obj->geolocation->getLat()));
            if($distance[0]->st_distance_spheroid > ($obj->interaction_radius))
            {
                return false;
            }
        }
        return true;
    }
}
