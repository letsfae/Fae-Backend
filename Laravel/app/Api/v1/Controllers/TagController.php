<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use App\Api\v1\Interfaces\RefInterface;

class TagController extends Controller implements RefInterface {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function create() {
    }

    public function getArray() {
    }

    public function getOne($tag_id) {
    }

    /**
     * only for controller
     */
    public static function ref($tag_id) {

    }

    public static function deref($tag_id) {

    }
}
