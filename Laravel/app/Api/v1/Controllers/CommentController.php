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
            $created_at = DATE($comments->created_at);
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

    public function getFromUser($user_id) {
        if (!is_numeric($user_id)) {
            throw new AccessDeniedHttpException('Bad request, Please type the correct user_id format!');
        }
        CommentController::getUserValidation($this->request);
        $start_time = $this->request->start_time;
        $end_time = $this->request->end_time;
        $page = $this->request->page;
        if ($start_time == null) {
            $start_time = '1970-01-01 00:00:00';
        }
        date_default_timezone_set("UTC");
        if ($end_time == null) {
            $end_time = date("Y-m-d H:i:s");
        }
        if ($page == null) {
            $page = '1';
        }
        $page = intval($page);
        $comments = Comments::where('user_id','=', $user_id)->where('created_at','>=', $start_time)->where('created_at','<=', $end_time)->orderBy('created_at', 'desc')->get();
        if ($comments == null) {
            throw new AccessDeniedHttpException('Bad request, No such comment exists!');
        }
        else {
            $total = $comments->count();
            $pages = intval($total / 30) + 1;
            if ($pages < $page) {
                throw new AccessDeniedHttpException('Bad request, Please select the correct page!');
            }
            $totalComments = array();
            $comment_id = array();
            $user_id = array();
            $created_at = array();
            $geolocation = array();
            $lat = array();
            $lng = array();
            $result = array();
            foreach ($comments as $comm) {
                $totalComments[] = $comm->content;
                $comment_id[] = $comm->id;
                $user_id[] = $comm->user_id;
                $created_at[] = date($comm->created_at);
                $geolocation[] = $comm->geolocation; 
            }
            foreach ($geolocation as $geo) {
                $lat[] = $geo->getLat();
                $lng[] = $geo->getLng();
            }
            if ($page * 30 > $total) {
                for ($i = ($page - 1) * 30; $i < $total; $i++) {
                    $result[] = array('page' => $page, 'total_pages' => $pages, 'comments' => array('comment_id' => $comment_id[$i], 'user_id' => $user_id[$i], 'content' => $totalComments[$i], 'geolocation' => array('latitude' => $lat[$i], 'longitude' => $lng[$i]), 'created_at' => $created_at[$i]));
                }
            }
            else {
                for ($i = ($page - 1) * 30; $i < ($page - 1) * 30 + 30; $i++) {
                    $result[] = array('page' => $page, 'total_pages' => $pages, 'comments' => array('comment_id' => $comment_id[$i], 'user_id' => $user_id[$i], 'content' => $totalComments[$i], 'geolocation' => array('latitude' => $lat[$i], 'longitude' => $lng[$i]), 'created_at' => $created_at[$i]));
                }
            }
            return $this->response->array($result);
        }
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
        $input = $request->all();
        $validator = Validator::make($input, [
            'start_time' => 'regex:/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/',
            'end_time' => 'regex:/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/',
        ]);
        if($validator->fails()) {
            throw new AccessDeniedHttpException('Bad request, Please verify your input!');
        }
    }
}

