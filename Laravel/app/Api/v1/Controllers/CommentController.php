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
use Auth;
use App\Api\v1\Interfaces\PinInterface;

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
        $comment->save();
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
        if($this->request->has('content'))
        {
            $comment->content = $this->request->content;
        }
        if($this->request->has('geo_longitude') && $this->request->has('geo_latitude'))
        {
            $comment->geolocation = new Point($this->request->geo_latitude, $this->request->geo_longitude);
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
        return $this->response->array(array('comment_id' => $comment->id, 'user_id' => $comment->user_id, 
                'content' => $comment->content, 'geolocation' => array('latitude' => $comment->geolocation->getLat(), 
                'longitude' => $comment->geolocation->getLng()), 'created_at' => $comment->created_at->format('Y-m-d H:i:s')));
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
        //date_default_timezone_set("UTC");
        $start_time = $this->request->has('start_time') ? $this->request->start_time:'1970-01-01 00:00:00';
        $end_time = $this->request->has('end_time') ? $this->request->end_time:date("Y-m-d H:i:s");
        $page =  $this->request->has('page') ? $this->request->page:1;
        $comments = Comments::where('user_id', $user_id)->where('created_at','>=', $start_time)->where('created_at','<=', $end_time)->orderBy('created_at', 'desc')->skip(30 * ($page - 1))->take(30)->get();
        $total = Comments::where('user_id', $user_id)
                        ->where('created_at','>=', $start_time)
                        ->where('created_at','<=', $end_time)
                        ->count();
        $total_pages = 0;
        if($total > 0)
        {
            $total_pages = intval(($total-1)/30)+1;
        }
        $info = array();
        foreach ($comments as $comment)
        {
            $info[] = array('comment_id' => $comment->id, 'user_id' => $comment->user_id, 'content' => $comment->content, 
                    'geolocation' => array('latitude' => $comment->geolocation->getLat(), 'longitude' => $comment->geolocation->getLng()), 
                    'created_at' => $comment->created_at->format('Y-m-d H:i:s'));    
        }
        return $this->response->array($info)->header('page', $page)->header('total_pages', $total_pages);
    }

    private function createValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'geo_longitude' => 'required|numeric|between:-180,180',
            'geo_latitude' => 'required|numeric|between:-90,90',
            'content' => 'required|string|max:500',
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not create comment.',$validator->errors());
        }
    }

    private function updateValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'geo_longitude' => 'filled|required_with:geo_latitude|required_without:content|numeric|between:-180,180',
            'geo_latitude' => 'filled|required_with:geo_longitude|required_without:content|numeric|between:-90,90',
            'content' => 'filled|required_without_all:geo_longitude,geo_latitude|string|max:500',
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

