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
        $tag = Tags:: where('title', $this->request->title)->first;
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
        // $hasType = $this->request->has('type') ? true : false;
        $total_pages = intval(Tags::count()/30) + 1;
        $info = array();
        // if($hasType)
        // {
        //     $tags = Tags::where('type', $this->request->type)->orderBy('reference_count','desc')->skip(30 * ($page - 1))->take(30)->get();
        // }
        $tags = Tags::orderBy('reference_count','desc')->orderBy('title','asc')->orderBy('id','asc')->skip(30 * ($page - 1))->take(30)->get();
        foreach ($tags as $tag)
        {
            $info[] = array('tag_id' => $tag->id, 'title' => $tag->title, 'color' => $tag->color);    
        }
        //return response($info, 200)->header('page', $page)->header('total_pages', $total_pages);
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
            return $this->response->errorNotFound();
        }
        $tag->reference_count++;
        $tag->save();
        return $this->response->created();
    }

    public static function deref($tag_id)
    {
        $tag = Tags::find($tag_id);
        if(is_null($tag))
        {
            return $this->response->errorNotFound();
        }
        if($tag->reference_count > 0)
        {
            $tag->reference_count--;
        }
        $tag->save();
        return $this->response->created();
    }

    private function createValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|alpha_dash:20',
            'color' => 'filled|string|regex:/^#\d{6}$/'
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
