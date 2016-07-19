<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use App\Api\v1\Interfaces\RefInterface;

class FileController extends Controller implements RefInterface {
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
    public static function ref($file_id) {

    }

    public static function deref($file_id) {

    }
}
