<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;

class UserNameCardController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function getNameCard($user_id) {

    }

    public function getSelfNameCard() {
        return $this->getNameCard($this->request->self_user_id);
    }

    public function getAllTags() {

    }

    public function updateNameCard() {

    }

}
