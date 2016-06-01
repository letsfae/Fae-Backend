<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;

class CommentController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function createComment() {
    }

    public function getComment($comment_id) {
    }

    public function deleteComment($comment_id) {
    }

    public function getUserComment($user_id) {
    }
}
