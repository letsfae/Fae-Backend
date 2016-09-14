<?php
namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PinOperationController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function save($type, $pin_id) {

    }

    public function unsave($type, $pin_id) {

    }

    public function like($type, $pin_id) {

    }

    public function unlike($type, $pin_id) {

    }

    public function comment($type, $pin_id) {

    }

    public function uncomment($type, $pin_id, $pin_comment_id) {

    }

    public function getPinAttribute($type, $pin_id) {

    }

    public function getPinCommentList($type, $pin_id) {

    }

    public function getUserPinList($user_id) {

    }

    public function getSelfPinList() {
        return $this->getUserPinList($this->request->self_user_id);
    }

    public function getSavedPinList() {
        
    }
}
