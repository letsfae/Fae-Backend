<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;

class FileController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function upload() {
    }

    public function getAttribute($file_id) {
    }

    public function getData($file_id) {
    }

    /**
     * only for controller
     */
    public function ref($file_id) {

    }

    public function deref($file_id) {

    }
}
