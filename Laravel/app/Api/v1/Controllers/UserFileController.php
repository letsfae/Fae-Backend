<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Dingo\Api\Routing\Helpers;


use Illuminate\Filesystem\Filesystem;
use Validator;

use Dingo\Api\Exception\StoreResourceFailedException;

class UserFileController extends Controller
{
    use Helpers;

    public function __construct(Request $request) {
    	$this->request = $request;
    }

    public function setSelfAvatar() {
    	// validation
    	$input = $this->request->all();
    	if(!$this->request->hasFile('avatar') || !$this->request->file('avatar')->isValid()) {
    		return $this->response->errorBadRequest();
    	}

    	// store file
    	$self_user_id = $this->request->self_user_id;
    	$file = $this->request->avatar;
		// $extension = $file->getClientOriginalExtension();
		Storage::disk('local')->put('avatar/'.$self_user_id.'.jpg', File::get($file));
        return $this->response->created();
    }

    public function getSelfAvatar() {
        // header('Content-Type', $entry->mime);
		return $this->getAvatar($this->request->self_user_id);
    }

    public function getAvatar($user_id) {
    	try {
			$file = Storage::disk('local')->get('avatar/'.$user_id.'.jpg');
		} catch(\Exception $e) {
			return $this->response->errorNotFound();
		}
		return response($file, 200)->header('Content-Type', 'image/jpeg');
    }

    public function setSelfNameCardCover() {
        // validation
        $input = $this->request->all();
        if(!$this->request->hasFile('name_card_cover') || !$this->request->file('name_card_cover')->isValid()) {
            return $this->response->errorBadRequest();
        }

        // store file
        $self_user_id = $this->request->self_user_id;
        $file = $this->request->avatar;
        // $extension = $file->getClientOriginalExtension();
        Storage::disk('local')->put('name_card_cover/'.$self_user_id.'.jpg', File::get($file));
        return $this->response->created();
    }

    public function getSelfNameCardCover() {
        return $this->getNameCardCover($this->request->self_user_id);
    }

    public function getNameCardCover($user_id) {
        try {
            $file = Storage::disk('local')->get('name_card_cover/'.$user_id.'.jpg');
        } catch(\Exception $e) {
            return $this->response->errorNotFound();
        }
        return response($file, 200)->header('Content-Type', 'image/jpeg');
    }

    public function updateNameCardPhoto() {
        // validation
    	$input = $this->request->all();
        $validator = Validator::make($input, [
            'position' => 'required|integer|min:1|max:8',
            'photo' => 'mimes:jpeg,jpg,png,gif|required|max:4096'
        ]);
        
        if($validator->fails()){
    		throw new StoreResourceFailedException('Could not update NameCard Photo.',$validator->errors());
    	}
        
        // store file
    	$self_user_id = $this->request->self_user_id;
    	$file = $this->request->photo;
        $position = $this->request->position;
        
		Storage::disk('local')->put('name_card_photo/'.$self_user_id.'_0'.$position, File::get($file));
        return $this->response->created();
    }

    public function deleteNameCardPhoto($position) {
        $self_user_id = $this->request->self_user_id;
        Storage::delete('name_card_photo/'.$self_user_id.'_0'.$position);
    }

    public function getNameCardPhoto($user_id, $position) {
    	try {
			$file = Storage::disk('local')->get('name_card_photo/'.$user_id.'_0'.$position);
            $mimetype = Storage::mimeType('name_card_photo/'.$user_id.'_0'.$position);
		} catch(\Exception $e) {
			return $this->response->errorNotFound();
		}
		return response($file, 200)->header('Content-Type', $mimetype);
    }

    public function getSelfNameCardPhoto($position) {
        return $this->getNameCardPhoto($this->request->self_user_id, $position);
    }
}
