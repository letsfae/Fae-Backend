<?php
namespace App\Api\v1\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Dingo\Api\Routing\Route;
use Dingo\Api\Auth\Provider\Authorization;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class FaeAuthorizationProvider extends Authorization
{
    // protected $auth;

    public function __construct()
    {
        // $this->auth = $auth;
    }

    public function authenticate(Request $request, Route $route)
    {
        $this->validateAuthorizationHeader($request);
        $authStr = $request->headers->get('authorization');
        if(Str::startsWith(strtolower($authStr), 'fae ')) {
            list($user_id, $token, $session_id) = explode(':', base64_decode(substr($authStr, 4)));
            // get user_id and token from session table with session_id
            if($user_id === '1' && $token === '123456') {
                $request->self_user_id = $user_id; // overwrite self_user_id
                return;
            }
        }

        throw new UnauthorizedHttpException('Invalid token');
    }

    public function getAuthorizationMethod()
    {
        return 'fae';
    }
}