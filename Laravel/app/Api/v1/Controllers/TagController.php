<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use App\Api\v1\Interfaces\RefInterface;
use Validator;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use App\Tags;

class TagController extends Controller implements RefInterface {
    use Helpers;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function create()
    {
        $this->createValidation($this->request);
        $tag = Tags:: where('title', $this->request->title)->first();
        if(is_null($tag))
        {
            $tag = new Tags();
            $tag->title = $this->request->title;
            $tag->user_id = $this->request->self_user_id;
            if($this->request->has('color'))
            {
                $tag->color = $this->request->color;
            }
            $tag->save();
        }
        return $this->response->created(null, array('tag_id' => $tag->id));
    }

    public function getArray()
    {
        $this->getArrayValidation($this->request);
        $page = $this->request->has('page') ? $this->request->page : 1;
        $total_pages = intval(Tags::count()/30) + 1;
        $info = array();
        $tags = Tags::orderBy('reference_count','desc')->orderBy('title','asc')->orderBy('id','asc')->skip(30 * ($page - 1))->take(30)->get();
        foreach ($tags as $tag)
        {
            $info[] = array('tag_id' => $tag->id, 'title' => $tag->title, 'color' => $tag->color);    
        }
        return $this->response->array($info)->header('page', $page)->header('total_pages', $total_pages);
    }

    public function getOne($tag_id)
    {
        $tag = Tags::find($tag_id);
        if(is_null($tag))
        {
            return $this->response->errorNotFound();
        }
        return $this->response->array(array('tag_id' => $tag->id, 'title' => $tag->title, 'color' => $tag->color));
    }

    /**
     * only for controller
     */
    public static function ref($tag_id)
    {
        $tag = Tags::find($tag_id);
        if(is_null($tag))
        {
            return false;
        }
        $tag->reference_count++;
        $tag->save();
        return true;
    }

    public static function deref($tag_id)
    {
        $tag = Tags::find($tag_id);
        if(is_null($tag))
        {
            return false;
        }
        if($tag->reference_count > 0)
        {
            $tag->reference_count--;
        }
        $tag->save();
        return true;
    }

    public static function exists($tag_id) {
        return Tags::where('id', $tag_id)->exists();
    }

    public static function refByString($tag_string) {
        $tag_ids = explode(';', $tag_string);
        foreach ($tag_ids as $tag_id)
        {
            TagController::ref($tag_id);
        }
    }

    public static function derefByString($tag_string) {
        $tag_ids = explode(';', $tag_string);
        foreach ($tag_ids as $tag_id)
        {
            TagController::deref($tag_id);
        }
    }

    public static function updateRefByString($old_tag_string, $new_tag_string) {
        $old_tag_ids = explode(';', $old_tag_string);
        $new_tag_ids = explode(';', $new_tag_string);
        foreach ($old_tag_ids as $tag_id)
        {
            TagController::deref($tag_id);
        }
        foreach ($new_tag_ids as $tag_id)
        {
            TagController::ref($tag_id);
        }
    }

    public static function existsByString($tag_string) {
        $tag_ids = explode(';', $tag_string);
        foreach($tag_ids as $tag_id) {
            if(!TagController::exists($tag_id)) {
                return false;
            }
        }
        return true;
    }

    private function createValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|alpha_dash:20', 
            'color' => 'filled|string|regex:/^#[0-9A-Fa-f]{6}$/' 
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not create new tag.', $validator->errors());
        }
    }

    private function getArrayValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'filled|integer'
        ]);
        if($validator->fails())
        {
            return $this->response->errorBadRequest();
        }
    }
}
