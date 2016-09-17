<?php
namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PinOperationController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function getUserPinList($user_id) {

    }

    public function getSelfPinList() {
    	return $this->getUserPinList($this->request->self_user_id);
    }

    public function getSavedPinList() {
        
    }

    public function savePin($pin_id) {

    }

    public function unsavePin($pin_id) {

    }

    public function likePin($pin_id) {

    }

    public function dislikePin($pin_id) {

    }
}
