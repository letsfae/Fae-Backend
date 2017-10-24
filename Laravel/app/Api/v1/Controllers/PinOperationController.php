<?php
namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Validator;
use App\Pin_operations;
use App\PinHelper;
use App\Comments;
use App\Medias;
use App\Pin_comments;
use App\PinCommentOperations;
use App\Sessions;
use App\Name_cards;
use App\Users;
use App\Places;
use App\Locations;
use App\ChatRooms;
use App\Collections;
use App\Collection_of_pins;
use DB;
use App\Api\v1\Utilities\ErrorCodeUtility;
use App\Api\v1\Utilities\PinUtility;

class PinOperationController extends Controller {
    use Helpers;
    const UNLIKE = 1;
    const UNCOMMENT = 2;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function save($collection_id, $type, $pin_id, $collection) {
        if(!is_numeric($pin_id))
        {
            return response()->json([
                    'message' => 'pin_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        if($type != 'media' && $type != 'comment' && $type != 'place' && $type != 'location')
        {
            return response()->json([
                'message' => 'wrong type, must be location, media, comment, or place',
                'error_code' => ErrorCodeUtility::WRONG_TYPE,
                'status_code' => '400'
            ], 400);
        }
        $obj_pin_operation = $this->readOperation($type, $pin_id);
        if(is_null($obj_pin_operation))
        {
            return response()->json([
                    'message' => 'PIN not found',
                    'error_code' => ErrorCodeUtility::PIN_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        $collection_of_pins = Collection_of_pins::where('collection_id', $collection_id)
                                                ->where('type', $type)->where('pin_id', $pin_id)
                                                ->first();
        if(!is_null($collection_of_pins)) {
            return response()->json([
                'message' => 'Bad request, you have already saved this pin in this collection!',
                'error_code' => ErrorCodeUtility::SAVED_ALREADY,
                'status_code' => '400'
            ], 400);
        }

        $collection_of_pins = new Collection_of_pins();
        $collection_of_pins->collection_id = $collection_id;
        $collection_of_pins->type = $type;
        $collection_of_pins->pin_id = $pin_id;
        $collection_of_pins->save();
        
        $obj_pin_operation->saved = PinUtility::AddIdToList($obj_pin_operation->saved, $collection_id);
        $obj_pin_operation->updateSavedTimestamp();
        $obj_pin_operation->save();

        $obj = self::getObj($type, $pin_id);
        $obj->saved_count++;
        $obj->save();

        $collection->count++;
        $collection->updateLastUpdatedAt();
        $collection->save();
        return $this->response->created();


    }

    public function unsave($collection_id, $type, $pin_id, $collection) {
        if(!is_numeric($pin_id))
        {
            return response()->json([
                    'message' => 'pin_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        if($type != 'media' && $type != 'comment' && $type != 'place' && $type != 'location')
        {
            return response()->json([
                'message' => 'wrong type, must be location, media, comment, or place',
                'error_code' => ErrorCodeUtility::WRONG_TYPE,
                'status_code' => '400'
            ], 400);
        } 
        $obj_pin_operation = $this->readOperation($type, $pin_id);
        if(is_null($obj_pin_operation))
        {
            return response()->json([
                    'message' => 'PIN not found',
                    'error_code' => ErrorCodeUtility::PIN_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        $collection_of_pins = Collection_of_pins::where('collection_id', $collection_id)
                                                ->where('type', $type)->where('pin_id', $pin_id)
                                                ->first();
        if(is_null($collection_of_pins)) {
            return response()->json([
                'message' => 'Bad request, you have not saved this pin in this collection yet!',
                'error_code' => ErrorCodeUtility::NOT_SAVED,
                'status_code' => '400'
            ], 400);
        }

        $collection_of_pins->delete();

        $obj_pin_operation->saved = PinUtility::deleteIdFromList($obj_pin_operation->saved, $collection_id);
        $obj_pin_operation->updateSavedTimestamp();
        $obj_pin_operation->save();
        
        $obj = self::getObj($type, $pin_id);
        $obj->saved_count--;
        $obj->save();

        $collection->count--;
        $collection->updateLastUpdatedAt();
        $collection->save();
        return $this->response->noContent();
    }

    public function feeling($type, $pin_id) {
        $this->feelingValidation($this->request);
        if(!is_numeric($pin_id))
        {
            return response()->json([
                    'message' => 'pin_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        if($type != 'media' && $type != 'comment')
        {
            return response()->json([
                'message' => 'wrong type, neither media nor comment',
                'error_code' => ErrorCodeUtility::WRONG_TYPE,
                'status_code' => '400'
            ], 400);
        }
        $obj_pin_operation = $this->readOperation($type, $pin_id);
        if(is_null($obj_pin_operation))
        {
            return response()->json([
                    'message' => 'PIN not found',
                    'error_code' => ErrorCodeUtility::PIN_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        $obj = self::getObj($type, $pin_id);
        if(is_null($obj)) 
        {
            return response()->json([
                    'message' => 'PIN not found',
                    'error_code' => ErrorCodeUtility::PIN_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        if($obj_pin_operation->feeling != -1) {
             $obj->feeling_count = PinUtility::decreaseFeelingCount($obj->feeling_count, $obj_pin_operation->feeling);
        }
        $obj_pin_operation->feeling = $this->request->feeling;
        $obj_pin_operation->updateFeelingTimestamp();
        $obj_pin_operation->save();
        $obj->feeling_count = PinUtility::increaseFeelingCount($obj->feeling_count, $this->request->feeling);
        $obj->save();
        return $this->response->created();
    }

    public function removeFeeling($type, $pin_id) {
        if(!is_numeric($pin_id))
        {
            return response()->json([
                    'message' => 'pin_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        if($type != 'media' && $type != 'comment')
        {
            return response()->json([
                'message' => 'wrong type, neither media nor comment',
                'error_code' => ErrorCodeUtility::WRONG_TYPE,
                'status_code' => '400'
            ], 400);
        }        
        $obj_pin_operation = $this->readOperation($type, $pin_id);
        if(is_null($obj_pin_operation))
        {
            return response()->json([
                    'message' => 'PIN not found',
                    'error_code' => ErrorCodeUtility::PIN_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        if($obj_pin_operation->feeling == -1)
        {
            return response()->json([
                'message' => 'Bad request, you have not post feeling of this pin yet!',
                'error_code' => ErrorCodeUtility::NO_FEELING,
                'status_code' => '400'
            ], 400);
        }
        $feeling = $obj_pin_operation->feeling;
        $obj_pin_operation->feeling = -1;
        $obj_pin_operation->updateFeelingTimestamp();
        $obj_pin_operation->save();
        $obj = self::getObj($type, $pin_id);
        $obj->feeling_count = PinUtility::decreaseFeelingCount($obj->feeling_count, $feeling);
        $obj->save();
        return $this->response->noContent();
    }

    public static function getOperations($type, $pin_id, $user_id)
    {
        $pin_operation = Pin_operations::where('pin_id', $pin_id)->where('user_id', $user_id)->
                         where('type', $type)->first();
        if($pin_operation == null)
        {
            return array('is_liked' => false, 'liked_timestamp' => null, 
                         'is_saved' => false, 'saved_timestamp' => null,
                         'feeling' => null, 'feeling_timestamp' => null,
                         'is_read'  => false, 'read_timestamp'  => null,
                         'memo' => null, 'memo_timestamp' => null);  
        }
        return array('is_liked' => $pin_operation->liked, 'liked_timestamp' => $pin_operation->liked_timestamp, 
                     'is_saved' => $pin_operation->saved, 'saved_timestamp' => $pin_operation->saved_timestamp,
                     'feeling' => $pin_operation->feeling, 'feeling_timestamp' => $pin_operation->feeling_timestamp,
                     'is_read'  => true, 'memo' => $pin_operation->memo,
                     'memo_timestamp' => $pin_operation->memo_timestamp,
                     'read_timestamp'  => $pin_operation->created_at->format('Y-m-d H:i:s'));
    }

    public function read($type, $pin_id)
    {
        if(!is_numeric($pin_id))
        {
            return response()->json([
                    'message' => 'pin_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        if($type != 'media' && $type != 'comment')
        {
            return response()->json([
                'message' => 'wrong type, neither media nor comment',
                'error_code' => ErrorCodeUtility::WRONG_TYPE,
                'status_code' => '400'
            ], 400);
        }
        $obj_pin_operation = $this->readOperation($type, $pin_id);
        if(is_null($obj_pin_operation))
        {
            return response()->json([
                    'message' => 'PIN not found',
                    'error_code' => ErrorCodeUtility::PIN_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
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
            else if($type == 'place')
            {
                $obj = Places::find((int)$pin_id);
            }
            else if($type == 'location')
            {
                $obj = Locations::find($pin_id);
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
                $newobj_pin_operation->saved = null;
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
        if(!is_numeric($pin_id))
        {
            return response()->json([
                    'message' => 'pin_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        if($type != 'media' && $type != 'comment')
        {
            return response()->json([
                'message' => 'wrong type, neither media nor comment',
                'error_code' => ErrorCodeUtility::WRONG_TYPE,
                'status_code' => '400'
            ], 400);
        }
        $obj_pin_operation = $this->readOperation($type, $pin_id);
        if(is_null($obj_pin_operation))
        {
            return response()->json([
                    'message' => 'PIN not found',
                    'error_code' => ErrorCodeUtility::PIN_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        if($obj_pin_operation->liked == true)
        {
            return response()->json([
                'message' => 'Bad request, you have already liked this pin!',
                'error_code' => ErrorCodeUtility::LIKED_ALREADY,
                'status_code' => '400'
            ], 400);
        }
        if($obj_pin_operation->interacted == false)
        {
            $inDistance = $this->checkDistance($this->request->self_user_id, $this->request->self_session_id, $type, $pin_id);
            if($inDistance === false)
            {
                return response()->json([
                    'message' => 'too far away',
                    'error_code' => ErrorCodeUtility::TOO_FAR_AWAY,
                    'status_code' => '403'
                ], 403);
            }
            if(is_null($inDistance))
            {
                if($inDistance == null)
                {
                    return response()->json([
                        'message' => 'location not found',
                        'error_code' => ErrorCodeUtility::LOCATION_NOT_FOUND,
                        'status_code' => '404'
                    ], 404);
                }
            }
        }
        $obj_pin_operation->liked = true;
        $obj_pin_operation->interacted = true;
        $obj_pin_operation->updateLikeTimestamp();
        $obj_pin_operation->save();
        $obj = self::getObj($type, $pin_id);
        $obj->liked_count++;
        $obj->save();
        return $this->response->created();
    }

    public function unlike($type, $pin_id)
    {
        if(!is_numeric($pin_id))
        {
            return response()->json([
                    'message' => 'pin_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        if($type != 'media' && $type != 'comment')
        {
            return response()->json([
                'message' => 'wrong type, neither media nor comment',
                'error_code' => ErrorCodeUtility::WRONG_TYPE,
                'status_code' => '400'
            ], 400);
        }
        $obj_pin_operation = $this->readOperation($type, $pin_id);
        if(is_null($obj_pin_operation))
        {
            return response()->json([
                    'message' => 'PIN not found',
                    'error_code' => ErrorCodeUtility::PIN_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        if($obj_pin_operation->liked == false) 
        {
            return response()->json([
                'message' => 'Bad request, you have not liked this pin yet!',
                'error_code' => ErrorCodeUtility::NOT_LIKED,
                'status_code' => '400'
            ], 400);
        }
        $obj_pin_operation->liked = false;
        $obj_pin_operation->updateLikeTimestamp();
        if($this->checkInteracted($type, $pin_id, self::UNLIKE) == false)
        {
            $obj_pin_operation->interacted = false;
        }
        $obj_pin_operation->save();
        $obj = self::getObj($type, $pin_id);
        $obj->liked_count--;
        $obj->save();
        return $this->response->noContent();
    }

    public function comment($type, $pin_id)
    {
        if(!is_numeric($pin_id))
        {
            return response()->json([
                    'message' => 'pin_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        if($type != 'media' && $type != 'comment')
        {
            return response()->json([
                'message' => 'wrong type, neither media nor comment',
                'error_code' => ErrorCodeUtility::WRONG_TYPE,
                'status_code' => '400'
            ], 400);
        }
        $validator = Validator::make($this->request->all(), [
            'content' => 'required|string',
            'anonymous' => 'filled|in:true,false'
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not comment.', $validator->errors());
        }
        $obj_pin_operation = $this->readOperation($type, $pin_id);
        if(is_null($obj_pin_operation))
        {
            return response()->json([
                    'message' => 'PIN not found',
                    'error_code' => ErrorCodeUtility::PIN_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        if($obj_pin_operation->interacted == false)
        {
            $inDistance = $this->checkDistance($this->request->self_user_id, $this->request->self_session_id, $type, $pin_id);
            if($inDistance === false)
            {
                return response()->json([
                    'message' => 'too far away',
                    'error_code' => ErrorCodeUtility::TOO_FAR_AWAY,
                    'status_code' => '403'
                ], 403);
            }
            if($inDistance == null)
            {
                return response()->json([
                    'message' => 'location not found',
                    'error_code' => ErrorCodeUtility::LOCATION_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
            }
        }
        $obj_pin_operation->interacted = true;
        $obj_pin_operation->save();
        $newobj_pin_comment = new Pin_comments;
        $newobj_pin_comment->user_id = $this->request->self_user_id;
        $newobj_pin_comment->pin_id = $pin_id;
        $newobj_pin_comment->type = $type;
        if($this->request->has('anonymous')) {
            $newobj_pin_comment->anonymous = $this->request->anonymous == 'true' ? true : false;
        }
        $newobj_pin_comment->content = $this->request->content;
        $newobj_pin_comment->save();
        $obj = self::getObj($type, $pin_id);
        $obj->comment_count++;
        $obj->save();
        $content = array('pin_comment_id' => $newobj_pin_comment->id);
        return $this->response->created(null, $content);
    }

    public function updateComment($pin_comment_id) {
        if(!is_numeric($pin_comment_id))
        {
            return response()->json([
                    'message' => 'pin_comment_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        $validator = Validator::make($this->request->all(), [
            'content' => 'filled|required_without:anonymous|string',
            'anonymous' => 'filled|required_without:content|in:true,false'
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not update comment.', $validator->errors());
        }
        $obj_pin_comment = Pin_comments::find($pin_comment_id);
        if (is_null($obj_pin_comment))
        {
            return response()->json([
                    'message' => 'comment not found',
                    'error_code' => ErrorCodeUtility::COMMENT_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        if($obj_pin_comment->user_id != $this->request->self_user_id)
        {
            return response()->json([
                    'message' => 'You can not update this comment',
                    'error_code' => ErrorCodeUtility::NOT_OWNER_OF_PIN,
                    'status_code' => '403'
                ], 403);
        }
        if($this->request->has('content')) {
            $obj_pin_comment->content = $this->request->content;
        }
        if($this->request->has('anonymous')) {
            $obj_pin_comment->anonymous = $this->request->anonymous == 'true' ? true : false;
        }
        $obj_pin_comment->save();
        return $this->response->created();
    }

    public function uncomment($pin_comment_id)
    {
        if(!is_numeric($pin_comment_id))
        {
            return response()->json([
                    'message' => 'pin_comment_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        $obj_pin_comment = Pin_comments::find($pin_comment_id);
        if (is_null($obj_pin_comment))
        {
            return response()->json([
                    'message' => 'comment not found',
                    'error_code' => ErrorCodeUtility::COMMENT_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        if($obj_pin_comment->user_id != $this->request->self_user_id)
        {
            return response()->json([
                    'message' => 'You can not delete this comment',
                    'error_code' => ErrorCodeUtility::NOT_OWNER_OF_PIN,
                    'status_code' => '403'
                ], 403);
        }
        $type = $obj_pin_comment->type;
        $pin_id = $obj_pin_comment->pin_id;
        $op = self::UNCOMMENT;
        PinCommentOperations::where('pin_comment_id', $obj_pin_comment->id)->delete();
        $obj_pin_comment->delete();
        $obj_pin_operation = $this->readOperation($type, $pin_id);
        if(is_null($obj_pin_operation))
        {
            return response()->json([
                    'message' => 'PIN not found',
                    'error_code' => ErrorCodeUtility::PIN_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        $obj_pin_operation->interacted = $this->checkInteracted($type, $pin_id, $op);
        $obj_pin_operation->save();
        $obj = self::getObj($type, $pin_id);
        $obj->comment_count--;
        $obj->save();
        return $this->response->noContent();
    }

    public function getPinAttribute($type, $pin_id)
    {
        if(!is_numeric($pin_id))
        {
            return response()->json([
                    'message' => 'pin_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        if($type != 'media' && $type != 'comment')
        {
            return response()->json([
                'message' => 'wrong type, neither media nor comment',
                'error_code' => ErrorCodeUtility::WRONG_TYPE,
                'status_code' => '400'
            ], 400);
        }
        $obj = self::getObj($type, $pin_id);
        if(is_null($obj))
        {
            return response()->json([
                    'message' => 'PIN not found',
                    'error_code' => ErrorCodeUtility::PIN_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        $content = array('type' => $type,
                         'pin_id'=> $obj->id,
                         'likes' => $obj->liked_count, 
                         'saves' => $obj->saved_count, 
                         'comments' => $obj->comment_count);
        return $this->response->created(null, $content);
    }

    public function getPinCommentList($type, $pin_id) {
        if(!is_numeric($pin_id))
        {
            return response()->json([
                    'message' => 'pin_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        if($type != 'media' && $type != 'comment')
        {
            return response()->json([
                'message' => 'wrong type, neither media nor comment',
                'error_code' => ErrorCodeUtility::WRONG_TYPE,
                'status_code' => '400'
            ], 400);
        }
        $obj = self::getObj($type, $pin_id);
        if(is_null($obj))
        {
            return response()->json([
                    'message' => 'PIN not found',
                    'error_code' => ErrorCodeUtility::PIN_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        $validator = Validator::make($this->request->all(), [
            'start_time' => 'filled|date_format:Y-m-d H:i:s|before:tomorrow',
            'end_time' => 'filled|date_format:Y-m-d H:i:s|before:tomorrow',
            'page' => 'filled|integer|min:1'
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not get user comments.',$validator->errors());
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
        if($total > 0)
        {
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
            $pin_comment_operations = $this->getPinCommentOperations($commented_pin->id, $this->request->self_user_id);
            $info[] = array('pin_comment_id' => $commented_pin->id,
                            'user_id' => ($commented_pin->anonymous && $commented_pin->user_id != $this->request->self_user_id) ? 
                            null : $commented_pin->user_id,
                            'anonymous' => $commented_pin->anonymous,
                            'nick_name' => ($commented_pin->anonymous && $commented_pin->user_id != $this->request->self_user_id) ? 
                            null : Name_cards::find($commented_pin->user_id)->nick_name,
                            'content' => $commented_pin->content,
                            'pin_comment_operations' => $pin_comment_operations,
                            'vote_up_count' => $commented_pin->vote_up_count,
                            'vote_down_count' => $commented_pin->vote_down_count,
                            'created_at' => $commented_pin->created_at->format('Y-m-d H:i:s'));
        }
        return $this->response->array($info)->header('page', $page)->header('total_pages', $total_pages);
    }

    private function getPinCommentOperations($pin_comment_id, $user_id)
    {
        $pin_comment_operation = PinCommentOperations::where('pin_comment_id', $pin_comment_id)
                                                     ->where('user_id', $user_id)->first();
        if($pin_comment_operation == null)
        {
            return array('vote' => null, 'vote_timestamp' => null);  
        }
        return array('vote' => $pin_comment_operation->vote == 1 ? 'up' : 'down',
                     'vote_timestamp' => $pin_comment_operation->vote_timestamp);
    }

    public function getUserPinList($user_id) 
    {
        if(!is_numeric($user_id))
        {
            return response()->json([
                    'message' => 'user_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        if (is_null(Users::find($user_id)))
        {
            return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        $validator = Validator::make($this->request->all(), [
            'start_time' => 'filled|date_format:Y-m-d H:i:s|before:tomorrow',
            'end_time' => 'filled|date_format:Y-m-d H:i:s|before:tomorrow',
            'page' => 'filled|integer|min:1'
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not get user pin list.',$validator->errors());
        }
        
        $start_time = $this->request->has('start_time') ? $this->request->start_time : '1970-01-01 00:00:00';
        $end_time = $this->request->has('end_time') ? $this->request->end_time : date("Y-m-d H:i:s");
        $page =  $this->request->has('page') ? $this->request->page : 1;
        $total = 0;
        $user_pin_helper_list = array();
        if($this->request->self_user_id == $user_id)
        {
            $total = PinHelper::where('user_id', $user_id)
                              ->where('created_at','>=', $start_time)
                              ->where('created_at','<=', $end_time)
                              ->count();
            $user_pin_helper_list = PinHelper::where('user_id', $user_id)
                                             ->where('created_at','>=', $start_time)
                                             ->where('created_at','<=', $end_time)
                                             ->orderBy('created_at', 'desc')
                                             ->skip(30 * ($page - 1))->take(30)->get();
        }
        else
        {
            $total = PinHelper::where('user_id', $user_id)
                              ->where('anonymous', false)
                              ->where('created_at','>=', $start_time)
                              ->where('created_at','<=', $end_time)
                              ->count();
            $user_pin_helper_list = PinHelper::where('user_id', $user_id)
                                             ->where('anonymous', false)
                                             ->where('created_at','>=', $start_time)
                                             ->where('created_at','<=', $end_time)
                                             ->orderBy('created_at', 'desc')
                                             ->skip(30 * ($page - 1))->take(30)->get();
        }
        $total_pages = 0;
        if($total > 0)
        {
            $total_pages = intval(( $total - 1 )/30) +1;
        }
        $info = array();
        foreach($user_pin_helper_list as $user_pin_helper)
        {
            $info[] = array('pin_id' => $user_pin_helper->pin_id,
                            'type' => $user_pin_helper->type,
                            'created_at' => $user_pin_helper->created_at->format('Y-m-d H:i:s'),
                            'pin_object' => PinUtility::getPinObject($user_pin_helper->type, $user_pin_helper->pin_id, 
                                $this->request->self_user_id));
        }
        return $this->response->array($info)->header('page', $page)->header('total_pages', $total_pages);
    }

    public function getSelfPinList()
    {
        return $this->getUserPinList($this->request->self_user_id);
    }

    public function getSavedPinList() 
    {
        $this->getSavedPinListValidation($this->request);
        $user_id = $this->request->self_user_id;
        $start_time = $this->request->has('start_time') ? $this->request->start_time : '1970-01-01 00:00:00';
        $end_time = $this->request->has('end_time') ? $this->request->end_time : date("Y-m-d H:i:s");
        $page =  $this->request->has('page') ? $this->request->page : 1;
        $is_place = $this->request->has('is_place') ? ($this->request->is_place == 'true' ? true : false) : false;
        if($is_place == true)
        {
            $total = Pin_operations::where('user_id', $user_id)
                               ->where('type', 'place')
                               ->whereNotNull('saved')
                               ->where('saved_timestamp','>=', $start_time)
                               ->where('saved_timestamp','<=', $end_time)
                               ->count();
        }
        else 
        {
            $total = Pin_operations::where('user_id', $user_id)
                               ->where('type', '!=', 'place')
                               ->whereNotNull('saved')
                               ->where('saved_timestamp','>=', $start_time)
                               ->where('saved_timestamp','<=', $end_time)
                               ->count();
        }
        $total_pages = 0;
        if($total > 0)
        {
            $total_pages = intval(( $total - 1 )/30) +1;
        }
        if($is_place == true)
        {
            $saved_pin_list = Pin_operations::where('user_id', $user_id)
                                        ->where('type', 'place')
                                        ->whereNotNull('saved')
                                        ->where('saved_timestamp','>=', $start_time)
                                        ->where('saved_timestamp','<=', $end_time)
                                        ->orderBy('saved_timestamp', 'desc')
                                        ->skip(30 * ($page - 1))->take(30)->get();
        }
        else
        {
            $saved_pin_list = Pin_operations::where('user_id', $user_id)
                                        ->where('type', '!=', 'place')
                                        ->whereNotNull('saved')
                                        ->where('saved_timestamp','>=', $start_time)
                                        ->where('saved_timestamp','<=', $end_time)
                                        ->orderBy('saved_timestamp', 'desc')
                                        ->skip(30 * ($page - 1))->take(30)->get();
        }
        $info = array();
        foreach($saved_pin_list as $saved_pin)
        {
            $info[] = array('pin_id' => $saved_pin->pin_id,
                            'type' => $saved_pin->type,
                            'created_at' => $saved_pin->saved_timestamp,
                            'pin_object' => PinUtility::getPinObject($saved_pin->type, $saved_pin->pin_id, 
                            $this->request->self_user_id),
                            'saved_collections' => $saved_pin->saved);
        }
        return $this->response->array($info)->header('page', $page)->header('total_pages', $total_pages);
    }

    public function vote($pin_comment_id)
    {
        $this->voteValidation($this->request);
        if(!is_numeric($pin_comment_id))
        {
            return response()->json([
                    'message' => 'pin_comment_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        $pin_comment = Pin_comments::find($pin_comment_id);
        if(is_null($pin_comment))
        {
            return response()->json([
                    'message' => 'comment not found',
                    'error_code' => ErrorCodeUtility::COMMENT_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        $pin_comment_operation = PinCommentOperations::where('pin_comment_id', $pin_comment_id)
                                 ->where('user_id', $this->request->self_user_id)->first();
        $vote = $this->request->vote == 'up' ? 1 : -1;
        if(is_null($pin_comment_operation))
        {
            $pin_comment_operation = new PinCommentOperations();
            $pin_comment_operation->pin_comment_id = $pin_comment_id;
            $pin_comment_operation->user_id = $this->request->self_user_id;
            $pin_comment_operation->vote = $vote;
            $pin_comment_operation->updateVoteTimestamp();
            $pin_comment_operation->save();
            if($pin_comment_operation->vote == 1)
            {
                $pin_comment->vote_up_count++;
            }
            else if($pin_comment_operation->vote == -1)
            {
                $pin_comment->vote_down_count++;
            }
        }
        else
        {
            if($pin_comment_operation->vote == 1 && $vote == 1)
            {
                return response()->json([
                    'message' => 'Bad request, you have already voted up!',
                    'error_code' => ErrorCodeUtility::VOTED_UP_ALREADY,
                    'status_code' => '400'
                ], 400);
            }
            else if($pin_comment_operation->vote == -1 && $vote == -1)
            {
                return response()->json([
                    'message' => 'Bad request, you have already voted down!',
                    'error_code' => ErrorCodeUtility::VOTED_DOWN_ALREADY,
                    'status_code' => '400'
                ], 400);
            }
            else if($pin_comment_operation->vote == 1 && $vote == -1)
            {
                $pin_comment->vote_up_count--;
                $pin_comment->vote_down_count++;
            }
            else if($pin_comment_operation->vote == -1 && $vote == 1)
            {
                $pin_comment->vote_up_count++;
                $pin_comment->vote_down_count--;
            }
            $pin_comment_operation->vote = $vote;
            $pin_comment_operation->updateVoteTimestamp();
            $pin_comment_operation->save();
        }
        $pin_comment->save();
        return $this->response->created();
    }

    public function cancelVote($pin_comment_id)
    {
        if(!is_numeric($pin_comment_id))
        {
            return response()->json([
                    'message' => 'pin_comment_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        $pin_comment = Pin_comments::find($pin_comment_id);
        if(is_null($pin_comment))
        {
            return response()->json([
                    'message' => 'comment not found',
                    'error_code' => ErrorCodeUtility::COMMENT_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        $pin_comment_operation = PinCommentOperations::where('pin_comment_id', $pin_comment_id)
                                 ->where('user_id', $this->request->self_user_id)->first();
        if(is_null($pin_comment_operation))
        {
            return response()->json([
                'message' => 'Bad request, you have not voted yet!',
                'error_code' => ErrorCodeUtility::NOT_VOTED,
                'status_code' => '400'
            ], 400);
        }
        if($pin_comment_operation->vote == 1)
        {
            $pin_comment->vote_up_count--;
        }
        else if($pin_comment_operation->vote == -1)
        {
            $pin_comment->vote_down_count--;
        }
        $pin_comment->save();
        $pin_comment_operation->delete();
        return $this->response->noContent();
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
        else if ($type == 'place')
        {
            $obj = Places::find((int)$pin_id);
        }
        else if ($type == 'location')
        {
            $obj = Locations::find($pin_id);
        }
        return $obj;
    }

    public static function checkDistance($user_id, $session_id, $type, $pin_id)
    {
        $obj = self::getObj($type, $pin_id);
        if($obj->interaction_radius == 0)
        {
            return true;
        }
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

    public function memo($type, $pin_id) {
        $this->memoVlidation($this->request);
        $obj_pin_operation = $this->readOperation($type, $pin_id);
        if(is_null($obj_pin_operation))
        {
            return response()->json([
                    'message' => 'PIN not found',
                    'error_code' => ErrorCodeUtility::PIN_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        $obj_pin_operation->memo = $this->request->content;
        $obj_pin_operation->save();
        return $this->response->created();
    }

    public function unmemo($type, $pin_id) {
        $obj_pin_operation = $this->readOperation($type, $pin_id);
        if(is_null($obj_pin_operation))
        {
            return response()->json([
                    'message' => 'PIN not found',
                    'error_code' => ErrorCodeUtility::PIN_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        $obj_pin_operation->memo = null;
        $obj_pin_operation->save();
        return $this->response->noContent();
    }

    public function memoVlidation(Request $request) {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string'
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not create this memo.',$validator->errors());
        }
    }

    private function voteValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vote' => 'required|string|in:up,down'
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not vote this comment.',$validator->errors());
        }
    }

    private function feelingValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'feeling' => 'required|integer|min:0|max:10'
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not update feeling',$validator->errors());
        }
    }

    public static function deletePinOperations($type, $pin_id)
    {
        Pin_operations::where('type', $type)->where('pin_id', $pin_id)->delete();
    }

    public static function deletePinComments($type, $pin_id)
    {
        //Pin_comments::where('type', $type)->where('pin_id', $pin_id)->delete();
        $pin_comments = Pin_comments::where('type', $type)->where('pin_id', $pin_id)->get();
        foreach ($pin_comments as $pin_comment) 
        {
            $this->deletePinCommentOperations($pin_comment->id);
            $pin_comment->delete();
        }
    }

    public static function deletePinCommentOperations($pin_comment_id)
    {
        PinCommentOperations::where('pin_comment_id', $pin_comment_id)->delete();
    }
    
    public function pinStatistics() {
        $count = array();
        $count['created_comment_pin'] = PinHelper::where('user_id', $this->request->self_user_id)->where('type', 'comment')->count();
        $count['created_media_pin'] = PinHelper::where('user_id', $this->request->self_user_id)->where('type', 'media')->count();
        $count['created_location'] = Locations::where('user_id', $this->request->self_user_id)->count();
        $count['created_chat_room'] = ChatRooms::where('user_id', $this->request->self_user_id)->count();
        $count['saved_comment_pin'] = Pin_operations::where('user_id', $this->request->self_user_id)
                                                    ->where('type', 'comment')
                                                    ->whereNotNull('saved')
                                                    ->count();
        $count['saved_media_pin'] = Pin_operations::where('user_id', $this->request->self_user_id)
                                                  ->where('type', 'media')
                                                  ->whereNotNull('saved')
                                                  ->count();
        $count['saved_place_pin'] = Pin_operations::where('user_id', $this->request->self_user_id)
                                                  ->where('type', 'place')
                                                  ->whereNotNull('saved')
                                                  ->count();
        $count['saved_location_pin'] = Pin_operations::where('user_id', $this->request->self_user_id)
                                                  ->where('type', 'location')
                                                  ->whereNotNull('saved')
                                                  ->count();
        return $this->response->array(array('user_id' => $this->request->self_user_id, 'count' => $count));
    }

    private function getSavedPinListValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'filled|date_format:Y-m-d H:i:s|before:tomorrow',
            'end_time' => 'filled|date_format:Y-m-d H:i:s|before:tomorrow',
            'page' => 'filled|integer|min:1',
            'is_place' => 'filled|in:true,false'
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not get user medias.',$validator->errors());
        }
    }
}