<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller;
use Auth;
use Validator;

use App\Api\v1\Utilities\ErrorCodeUtility;

class CollectionController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function createOne() {

    }

    public function getAll() {

    }

    public function updateOne($collection_id) {

    }

    public function deleteOne($collection_id) {

    }

    public function getOne($collection_id) {

    }

    public function save($collection_id, $type, $pin_id) {

    }

    public function unsave($collection_id, $type, $pin_id) {

    }
}
