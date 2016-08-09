<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use App\Users;
use App\Name_cards;
use App\Name_card_tags;
use Validator;
use Dingo\Api\Exception\UpdateResourceFailedException;

class UserNameCardController extends Controller
{
    use Helpers;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getNameCard($user_id)
    {
        $nameCard = Name_cards::find($user_id);
        if(is_null($nameCard))
        {
            return $this->response->errorNotFound();
        }
        $gender = Name_cards::find($user_id)->hasOneUser()->first()->gender;
        $tags = array();
        if($nameCard->tag_ids != null)
        {
            foreach (explode(';', $nameCard->tag_ids) as $tag) 
            { 
                $tag = Name_card_tags::find($tag);
                $tags[] = array('tag_id' => $tag->id, 'title' => $tag->title, 'color' => $tag->color); 
            }
        }
        $info = array('nick_name' => $nameCard->nick_name, 'short_intro' => $nameCard->short_intro, 'tags' => $tags, 'gender' => $gender);
        return $this->response->array($info);
    }

    public function getSelfNameCard()
    {
        return $this->getNameCard($this->request->self_user_id);
    }

    public function getAllTags()
    {
        $tags = Name_card_tags::all();
        $info = array();
        foreach ($tags as $tag)
        {
            $info[] = array('tag_id' => $tag->id, 'title' => $tag->title, 'color' => $tag->color);
        }
        return $this->response->array($info);
    }

    public function updateNameCard()
    {
        $this->updateNameCardValidation($this->request);
        $nameCard = Name_cards::find($this->request->self_user_id);
        if($this->request->has('nick_name'))
        {
            $nameCard->nick_name = $this->request->nick_name;
        }
        if($this->request->has('short_intro'))
        {
            $nameCard->short_intro = $this->request->short_intro;
        }
        if(!is_null($this->request->short_intro) && empty($this->request->short_intro))
        {
            $nameCard->short_intro = null;
        }
        if($this->request->has('tag_ids'))
        {
            foreach (explode(';', $this->request->tag_ids) as $tag)
            {
                if(is_null(Name_card_tags::find($tag)))
                {
                    return $this->response->errorBadRequest('tag doest not exist');
                }
            }   
            $nameCard->tag_ids = $this->request->tag_ids;
        }
        $nameCard->save();
        return $this->response->created();
    }

    private function updateNameCardValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nick_name' => 'filled|required_without_all:short_intro,tag_ids|alpha_num:50',
            'short_intro' => 'filled|required_without_all:nick_name,tag_ids|string|max:200',
            'tag_ids' => 'filled|required_without_all:nick_name,short_intro|regex:/^(\d+\;){0,2}\d+$/'
        ]);
        if($validator->fails())
        {
            if(!is_null($request->short_intro) && empty($request->short_intro))
            {
                if($request->has('nick_name') || $request->has('tag_ids'))
                {
                    $validator = Validator::make($request->all(), [
                        'nick_name' => 'filled|alpha_num:50',
                        'tag_ids' => 'filled|regex:/^(\d+\;){0,2}\d+$/'
                    ]);
                    if($validator->fails())
                    {
                        throw new UpdateResourceFailedException('Could not update name card.',$validator->errors());
                    }
                }
                return;
            }
            throw new UpdateResourceFailedException('Could not update name card.',$validator->errors());
        }
    }
}
