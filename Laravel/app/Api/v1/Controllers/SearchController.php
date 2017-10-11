<?php
namespace App\Api\v1\Controllers;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use App\Users;
use App\Api\v1\Utilities\ErrorCodeUtility;

class SearchController extends Controller {
    use Helpers;
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function search() {

    }

    public function bulk() {

    }
}
