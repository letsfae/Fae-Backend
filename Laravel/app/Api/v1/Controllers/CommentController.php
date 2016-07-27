<?php

namespace App\Api\v1\Controllers;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Validator;
use Config;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use App\Comments;
use Auth;
use App\Api\v1\Interfaces\PinInterface;

class CommentController extends Controller implements PinInterface {
    use Helpers;
    use PostgisTrait;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function create() {
        CommentController::createValidation($this->request);
        $lat = $this->request->geo_latitude;
        $lng = $this->request->geo_longitude;
        $content = $this->request->content;
        $user_id = $this->request->self_user_id;
        $comments = new Comments();
        $comments->user_id = $user_id;
        $comments->content = $content;
        $comments->geolocation = new Point($lat, $lng);
        $comments->save();
        $comment_id = $comments->id;
        $comment = array('comment_id' => $comment_id);
        return $this->response->created(null, $comment);
    }

    public function update($comment_id) {
        
    }

    public function getOne($comment_id) {
        if (!is_numeric($comment_id)) {
            throw new AccessDeniedHttpException('Bad request, Please type the correct comment_id format!');
        }
        $comments = Comments::where('id', '=', $comment_id)->first();
        if ($comments == null) {
            throw new AccessDeniedHttpException('Bad request, No such comments exist!');
        }
        else {
            $user_id = $comments->user_id;
            $comment_id = intval($comment_id);
            $content = $comments->content;
            $geolocation = $comments->geolocation;
            $lat = $geolocation->getLat();
            $lng = $geolocation->getLng();
            $created_at = $comments->created_at->format('Y-m-d H:i:s');
            $result = array('comment_id' => $comment_id, 'user_id' => $user_id, 'content' => $content, 'geolocation' => array('latitude' => $lat, 'longitude' => $lng), 'created_at' => $created_at);
            return $this->response->array($result);
        }
    }

    public function delete($comment_id) {
        if (!is_numeric($comment_id)) {
            throw new AccessDeniedHttpException('Bad request, Please type the correct comment_id format!');
        }
        $comments = Comments::where('id', '=', $comment_id)->first();
        if ($comments == null) {
            throw new DeleteResourceFailedException('Delete failed');
        } 
        else {
            $comments->delete();
        }
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
        $total_pages = intval($comments->count() / 30) + 1;
        $info = array();
        foreach ($comments as $comment)
        {
            $info[] = array('comment_id' => $comment->id, 'user_id' => $comment->user_id, 'content' => $comment->content, 'geolocation' => array('latitude' => $comment->geolocation->getLat(), 'longitude' => $comment->geolocation->getLng()), 'created_at' => $comment->created_at->format('Y-m-d H:i:s'));    
        }
        return $this->response->array($info)->header('page', $page)->header('total_pages', $total_pages);
    }

    private function createValidation(Request $request) {
        $input = $request->all();
        $validator = Validator::make($input, [
            'geo_longitude' => 'required|numeric|between:-180,180',
            'geo_latitude' => 'required|numeric|between:-90,90',
            'content' => 'required|max:500',
        ]);
        if($validator->fails()) {
            throw new AccessDeniedHttpException('Bad request, Please verify your input!');
        }
    }

    private function getUserValidation(Request $request) {
        $validator = Validator::make($request->all(), [
            'start_time' => 'filled|date_format:Y-m-d H:i:s|before:tomorrow',
            'end_time' => 'filled|date_format:Y-m-d H:i:s|before:tomorrow',
            'page' => 'filled|integer|min:1'
        ]);
        if($validator->fails()) {
            throw new UpdateResourceFailedException('Could not get user comments.',$validator->errors());
        }
    }
}

