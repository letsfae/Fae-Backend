<?php

namespace App\Api\v1\Controllers;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Validator;
use Config;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use App\Comments;
use App\Users;
use App\PinHelper;
use App\Api\v1\Controllers\PinOperationController;
use Auth;
use App\Api\v1\Interfaces\PinInterface;
use App\Api\v1\Controllers\RichTextController;

class CommentController extends Controller implements PinInterface
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
        $comment = new Comments();
        $comment->user_id = $this->request->self_user_id;
        $comment->content = $this->request->content;
        $comment->geolocation = new Point($this->request->geo_latitude, $this->request->geo_longitude);
        $comment->duration = $this->request->duration;
        $pin_helper = new PinHelper();
        if($this->request->has('interaction_radius'))
        {
            $comment->interaction_radius = $this->request->interaction_radius;
        }
        if($this->request->has('anonymous'))
        {
            $comment->anonymous = $this->request->anonymous == 'true' ? true : false;
            $pin_helper->anonymous = $comment->anonymous;
        }
        $comment->save();
        // pin helper
        $pin_helper->type = 'comment';
        $pin_helper->user_id = $this->request->self_user_id;
        $pin_helper->geolocation =  new Point($this->request->geo_latitude, $this->request->geo_longitude);
        $pin_helper->pin_id = $comment->id;
        $pin_helper->duration = $comment->duration;
        $pin_helper->save();
        
        //---------------- for hashtag ----------------
        $comment->processRichtext();
        //$comment->updateRichtext($comment->content);
        //RichTextController::updateHashtagFromRichtext($comment->id, $comment->content, $comment->content, 'Comments');
        //var_dump(RichTextController::getRichtextWithHashtagID(1, 'Comments'));
        //var_dump(RichTextController::getRichtextWithHashtagContext('#asdasdaas', 'Comments'));
        //---------------------------------------------
        return $this->response->created(null, array('comment_id' => $comment->id));
    }

    public function update($comment_id)
    {
        if(!is_numeric($comment_id))
        {
            return $this->response->errorBadRequest();
        }
        $this->updateValidation($this->request);
        $comment = Comments::find($comment_id);
        if(is_null($comment))
        {
            return $this->response->errorNotFound();
        }
        if($comment->user_id != $this->request->self_user_id)
        {
            throw new AccessDeniedHttpException('You can not update this comment');
        }
        if($this->request->has('content'))
        {
            $comment->content = $this->request->content;
            $comment->save();
            $comment->updateRichtext($this->request->content);
        }
        if($this->request->has('geo_longitude') && $this->request->has('geo_latitude'))
        {
            $comment->geolocation = new Point($this->request->geo_latitude, $this->request->geo_longitude);
            $pin_helper = PinHelper::where('pin_id', $comment_id)->where('type', 'comment')->first();
            $pin_helper->geolocation = new Point($this->request->geo_latitude, $this->request->geo_longitude);
            $pin_helper->save();
        }
        if($this->request->has('duration'))
        {
            $comment->duration = $this->request->duration;
            $pin_helper = PinHelper::where('pin_id', $comment_id)->where('type', 'comment')->first();
            $pin_helper->duration = $comment->duration;
            $pin_helper->save();
        }
        if($this->request->has('interaction_radius'))
        {
            $comment->interaction_radius = $this->request->interaction_radius;
        }
        if($this->request->has('anonymous'))
        {
            $comment->anonymous = $this->request->anonymous == 'true' ? true : false;
            $pin_helper = PinHelper::where('pin_id', $comment_id)->where('type', 'comment')->first();
            $pin_helper->anonymous = $comment->anonymous;
            $pin_helper->save();
        }
        $comment->save();
        return $this->response->created();
    }

    public function getOne($comment_id)
    {
        if(!is_numeric($comment_id))
        {
            return $this->response->errorBadRequest();
        }
        $comment = Comments::find($comment_id);
        if(is_null($comment))
        {
            return $this->response->errorNotFound();
        }
        $user_pin_operations = PinOperationController::getOperations('comment', $comment_id, $this->request->self_user_id);
        return $this->response->array(array('comment_id' => $comment->id, 
                'user_id' => ($comment->anonymous && $comment->user_id != $this->request->self_user_id) ? null : $comment->user_id, 
                'content' => $comment->content, 'geolocation' => array('latitude' => $comment->geolocation->getLat(), 
                'longitude' => $comment->geolocation->getLng()), 'liked_count' => $comment->liked_count, 
                'saved_count' => $comment->saved_count, 'comment_count' => $comment->comment_count,
                'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                'user_pin_operations' => $user_pin_operations));
    }

    public function delete($comment_id)
    {
        if(!is_numeric($comment_id))
        {
            return $this->response->errorBadRequest();
        }
        $comment = Comments::find($comment_id);
        if(is_null($comment))
        {
            return $this->response->errorNotFound();
        }
        if($comment->user_id != $this->request->self_user_id)
        {
            throw new AccessDeniedHttpException('You can not delete this comment');
        }

        // pin helper
        $pin_helper = PinHelper::where('pin_id', $comment_id)->where('type', 'comment')->first();
        $pin_helper->delete();
        PinOperationController::deletePinOperations('comment', $comment->id);
        PinOperationController::deletePinComments('comment', $comment->id);
        // richtext        
        $comment->deleteRichtext($comment->content);
        $comment->delete();
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
        $this->getUserValidation($this->request);
        $start_time = $this->request->has('start_time') ? $this->request->start_time:'1970-01-01 00:00:00';
        $end_time = $this->request->has('end_time') ? $this->request->end_time:date("Y-m-d H:i:s");
        $page =  $this->request->has('page') ? $this->request->page:1;
        $comments = array();
        $total = 0;
        if($this->request->self_user_id == $user_id)
        {
            $comments = Comments::where('user_id', $user_id)
                                ->where('created_at','>=', $start_time)
                                ->where('created_at','<=', $end_time)
                                ->orderBy('created_at', 'desc')
                                ->skip(30 * ($page - 1))->take(30)->get();
            $total = Comments::where('user_id', $user_id)
                             ->where('created_at','>=', $start_time)
                             ->where('created_at','<=', $end_time)
                             ->count();
        }
        else
        {
            $comments = Comments::where('user_id', $user_id)
                                ->where('anonymous', false)
                                ->where('created_at','>=', $start_time)
                                ->where('created_at','<=', $end_time)
                                ->orderBy('created_at', 'desc')
                                ->skip(30 * ($page - 1))->take(30)->get();
            $total = Comments::where('user_id', $user_id)
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
        foreach ($comments as $comment)
        {
            $user_pin_operations = PinOperationController::getOperations('comment', $comment->id, $this->request->self_user_id);
            $info[] = array('comment_id' => $comment->id, 
                    'user_id' => $comment->user_id,
                    'content' => $comment->content, 'geolocation' => array('latitude' => $comment->geolocation->getLat(), 
                    'longitude' => $comment->geolocation->getLng()), 'liked_count' => $comment->liked_count, 
                    'saved_count' => $comment->saved_count, 'comment_count' => $comment->comment_count,
                    'created_at' => $comment->created_at->format('Y-m-d H:i:s'), 'user_pin_operations' => $user_pin_operations);    
        }
        return $this->response->array($info)->header('page', $page)->header('total_pages', $total_pages);
    }

    private function createValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'geo_longitude' => 'required|numeric|between:-180,180',
            'geo_latitude' => 'required|numeric|between:-90,90',
            'content' => 'required|string|max:500',
            'duration' => 'required|int|min:0',
            'interaction_radius' => 'filled|int|min:0',
            'anonymous' => 'filled|in:true,false'
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not create comment.',$validator->errors());
        }
    }

    private function updateValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'geo_longitude' => 'filled|required_with:geo_latitude|required_without:content,duration,interaction_radius,anonymous|
                                numeric|between:-180,180',
            'geo_latitude' => 'filled|required_with:geo_longitude|required_without:content,duration,interaction_radius,anonymous|
                                numeric|between:-90,90',
            'content' => 'filled|required_without_all:geo_longitude,geo_latitude,duration,interaction_radius,anonymous|string|max:500',
            'duration' => 'filled|required_without_all:geo_longitude,geo_latitude,content,interaction_radius,anonymous|int|min:0',
            'interaction_radius' => 'filled|required_without_all:geo_longitude,geo_latitude,duration,content,anonymous|int|min:0',
            'anonymous' => 'filled|required_without_all:geo_longitude,geo_latitude,duration,content,interaction_radius|in:true,false'
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not update comment.',$validator->errors());
        }
    }

    private function getUserValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'filled|date_format:Y-m-d H:i:s|before:tomorrow',
            'end_time' => 'filled|date_format:Y-m-d H:i:s|before:tomorrow',
            'page' => 'filled|integer|min:1'
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not get user comments.',$validator->errors());
        }
    }
}

