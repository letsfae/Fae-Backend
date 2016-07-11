<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Dingo\Api\Routing\Helpers;

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


    public function updateNameCardPhoto() {

    }

    public function deleteNameCardPhoto($position) {

    }

    public function getNameCardPhoto($user_id, $position) {

    }

    public function getSelfNameCardPhoto($position) {
        return $this->getNameCardPhoto($this->request->self_user_id, $position);
    }
}
