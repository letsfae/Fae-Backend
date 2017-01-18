<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use App\Api\v1\Interfaces\RefInterface;
use Validator;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use DB;
use App\Hashtags;
use App\Richtext_one;
use App\Users;

class RichTextController extends Controller {
    use Helpers;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    public function searchHashtag($hashtag){
        
        preg_match_all('/(\w+)/u', $hashtag, $matches);
        
        if ( !$matches || !($matches[0][0]===$hashtag)){
            throw new StoreResourceFailedException('Hashtag formate wrong.');
        }
        
        $content = array();
        $org_hashtag = '#'.$hashtag;
    
        $existing_hashtag = Hashtags::where('context', $org_hashtag)->first();
        if(is_null($existing_hashtag))
            $content = array('hashtag_id' => null, 'context' => null, 'reference_count' => null);
        else
            $content = array('hashtag_id' => $existing_hashtag->id, 'context' => $org_hashtag, 'reference_count' => $existing_hashtag->reference_count);
        
        return $this->response->array($content);
    }
    
    public function searchHashtagWithText($hashtag){
        preg_match_all('/(\w+)/u', $hashtag, $matches);
        
        if ( !$matches || !($matches[0][0]===$hashtag)){
            throw new StoreResourceFailedException('Hashtag formate wrong.');
        }
        
        $org_hashtag = '#'.$hashtag;
        
        $hashtag_list = Hashtags::where( 'context', '~', $hashtag)
                                    ->where( 'context', '!=', $org_hashtag)
                                    ->orderBy('reference_count', 'desc')
                                    ->take(30)
                                    ->get();
        
        $info = array();
        foreach($hashtag_list as $obj_hashtag)
        {
            $info[] = array('hashtag_id' => $obj_hashtag->id,
                            'context' => $obj_hashtag->context,
                            'reference_count' => $obj_hashtag->reference_count);
        }
        
        return $this->response->array($info);
    }

    public static function addHashtagFromRichtext($obj_id, $richtext, $className){
        
        $obj = ('App\\'.$className)::find($obj_id);
        
        preg_match_all('/(\<hashtag\>#\w+\<\/hashtag\>)/u', $richtext, $matches, PREG_OFFSET_CAPTURE);
        $content = array();
        if ($matches) {
            foreach ($matches[0] as $match){
                preg_match('/(#\w+)/u', $match[0], $context);
                
                if (!Hashtags::where('context', $context[0])->exists()){
                    $hashtag = new Hashtags();
                    $hashtag->context = $context[0];
                    $hashtag->reference_count = 1;
                    $hashtag->save();
                    $obj->hashtags()->save($hashtag);
                    $content[] = array('hashtag_id' => $hashtag->id, 'context' => $context[0]);
                }else{
                    $hashtag = Hashtags::where('context', $context[0])->first();
                    $hashtag->reference_count++;
                    $obj->hashtags()->save($hashtag);
                    $content[] = array('hashtag_id' => $hashtag->id, 'context' => $context[0]);
                }
            }
        }
        
        return $content;
    }
    
    public static function deleteHashtagFromRichtext($obj_id, $old_richtext, $className){
        
        $obj = ('App\\'.$className)::find($obj_id);
        
        preg_match_all('/(\<hashtag\>#\w+\<\/hashtag\>)/u', $old_richtext, $matches, PREG_OFFSET_CAPTURE);
        if ($matches) {
            foreach ($matches[0] as $match){
                preg_match('/(#\w+)/u', $match[0], $context);
                
                if (Hashtags::where('context', $context[0])->exists()){
                    $hashtag = Hashtags::where('context', $context[0])->first();
                    $hashtag->reference_count--;
                    $hashtag->save();
                    $obj->hashtags()->detach();
                    
                }
            }
        }
        
        //return RichTextController::addHashtagFromRichtext($obj_id, $richtext, $className);
    }
    
    public static function atUsersFromRichtext($obj_id, $richtext, $className){
        
        $obj = ('App\\'.$className)::find($obj_id);
        
        preg_match_all('/(\<a\>\@\w+\<\/a\>)/u', $richtext, $matches, PREG_OFFSET_CAPTURE);
        
        $content = array();
        if ($matches) {
            foreach ($matches[0] as $match){
                preg_match('/(\@\w+)/u', $match[0], $context);
                //echo substr($context[0],1);
                $user = Users::where('user_name', substr($context[0],1))->first();
                if(is_null($user)){
                    return $content;
                }
                $obj->ats()->save($user);
                $content[] = array('user_id' => $user->id, 'context' => $context[0]);
                
            }
        }
        
        return $content;
    }
    
    public static function deleteAtUsersFromRichtext($obj_id, $old_richtext, $className){
        
         $obj = ('App\\'.$className)::find($obj_id);
        
        preg_match_all('/(\<a\>@\w+\<\/a\>)/u', $old_richtext, $matches, PREG_OFFSET_CAPTURE);
        $content = array();
        if ($matches) {
            foreach ($matches[0] as $match){
                preg_match('/(\@\w+)/u', $match[0], $context);
                //echo substr($context[0],1);
                $user = Users::where('user_name', substr($context[0],1))->first();
                $obj->ats()->detach();
            }
        }
        
        //return RichTextController::atUsersFromRichtext($obj_id, $richtext, $className);
    }
    
    public static function getRichtextWithHashtagID($hashtag_id, $className){
        $hashtag = Hashtags::find($hashtag_id);
        
        if(is_null($hashtag)){
            throw new StoreResourceFailedException('Could not get the Hashtag.');
        }
        $richtexts = $hashtag->target_obj($className)->get();
        
        
        $content = array();
        
        foreach ($richtexts as $richtext_one) {
            $content[] = array('id' => $richtext_one->id);
        }
        
        return $content;
    }
    
    public static function getRichtextWithHashtagContext($hashtag_context, $className){
        $hashtag = Hashtags::where('context', $hashtag_context)->first();
        
        if(is_null($hashtag)){
            throw new StoreResourceFailedException('Could not get the Hashtag.');
        }
        $richtexts = $hashtag->target_obj($className)->get();
        
        
        $content = array();
        
        foreach ($richtexts as $richtext_one) {
            $content[] = array('id' => $richtext_one->id);
        }
        
        return $content;
    }

    
}
