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

class PinOperationController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function save($type, $pin_id) {
        $obj_pin_operation = Pin_operations::where('pin_id', $pin_id)->where('user_id', $this->request->self_user_id)->where('type', $type)->first();
        if ($obj_pin_operation == null) {
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
                        
            $newobj_pin_operation = new Pin_operations;
            $newobj_pin_operation->user_id = $this->request->self_user_id;
            $newobj_pin_operation->pin_id = $pin_id;
            $newobj_pin_operation->type = $type;
            $newobj_pin_operation->saved = true;
            $newobj_pin_operation->updateSavedTimestamp();
            $newobj_pin_operation->save();
        }else{
            $obj_pin_operation->saved = true;
            $obj_pin_operation->updateSavedTimestamp();
            $obj_pin_operation->save(); 
        }
        return $this->response->created();
    }

    public function unsave($type, $pin_id) {
        $obj_pin_operation = Pin_operations::where('pin_id', $pin_id)->where('user_id', $this->request->self_user_id)->where('type', $type)->first();
        if ($obj_pin_operation == null) {
            throw new StoreResourceFailedException('Bad request, never saved such pin!');
        }else{
            if($obj_pin_operation->liked == true){
                $obj_pin_operation->saved = false;
                $obj_pin_operation->updateSavedTimestamp();
                $obj_pin_operation->save(); 
            }else{
                $obj_pin_operation->delete();
            }
        }
        return $this->response->noContent();
    }

    public static function isSavedisLiked($type, $pin_id, $user_id)
    {
        $pin_operation = Pin_operations::where('pin_id', $pin_id)->where('user_id', $user_id)->
                         where('type', $type)->first();
        if($pin_operation == null)
        {
            return array('is_liked' => false, 'liked_timestamp' => null, 'is_saved' => false, 'saved_timestamp' => null);
        }
        return array('is_liked' => $pin_operation->liked, 'liked_timestamp' => $pin_operation->liked_timestamp, 
            'is_saved' => $pin_operation->saved, 'saved_timestamp' => $pin_operation->saved_timestamp);
    }

    public function read($type, $pin_id) {
    }

    public function like($type, $pin_id) {
        $obj_pin_operation = Pin_operations::where('pin_id', $pin_id)->where('user_id', $this->request->self_user_id)->where('type', $type)->first();
        if ($obj_pin_operation == null) {
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
                        
            $newobj_pin_operation = new Pin_operations;
            $newobj_pin_operation->user_id = $this->request->self_user_id;
            $newobj_pin_operation->pin_id = $pin_id;
            $newobj_pin_operation->type = $type;
            $newobj_pin_operation->liked = true;
            $newobj_pin_operation->updateLikeTimestamp();
            $newobj_pin_operation->save();
        }else{
            $obj_pin_operation->liked = true;
            $obj_pin_operation->updateLikeTimestamp();
            $obj_pin_operation->save(); 
        }
        return $this->response->created();
    }

    public function unlike($type, $pin_id) {
        $obj_pin_operation = Pin_operations::where('pin_id', $pin_id)->where('user_id', $this->request->self_user_id)->where('type', $type)->first();
        if ($obj_pin_operation == null) {
            throw new StoreResourceFailedException('Bad request, never liked such pin!');
        }else{
            if($obj_pin_operation->saved == true){
                $obj_pin_operation->liked = false;
                $obj_pin_operation->updateLikeTimestamp();
                $obj_pin_operation->save(); 
            }else{
                $obj_pin_operation->delete();
            }
        }
        return $this->response->noContent();
    }

    public function comment($type, $pin_id) {
        $validator = Validator::make($this->request->all(), [
            'content' => 'required|string|max:100'
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not comment.', $validator->errors());
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

        $newobj_pin_comment = new Pin_comments;
        $newobj_pin_comment->user_id = $this->request->self_user_id;
        $newobj_pin_comment->pin_id = $pin_id;
        $newobj_pin_comment->type = $type;
        $newobj_pin_comment->content = $this->request->content;
        $newobj_pin_comment->save();
        
        $content = array('pin_comment_id' => $newobj_pin_comment->id);
        return $this->response->created(null, $content);
    }

    public function uncomment($pin_comment_id) {
        $obj_pin_comment = Pin_comments::where('id', $pin_comment_id)->first();
        if ($obj_pin_comment == null) {
            throw new StoreResourceFailedException('Bad request, never commented such pin!');
        }else{
            $obj_pin_comment->delete();
        }
        return $this->response->noContent();
    }

    public function getPinAttribute($type, $pin_id) {
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
}
