<?php

namespace App\Api\v1\Controllers;
use Validator;
use Config;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;

class LocationController extends Controller implements PinInterface
{
    use Helpers;
    use PostgisTrait;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function create()
    {
    }

    public function update($location_id)
    {
    }

    public function getOne($location_id)
    {
    }

    public function delete($location_id)
    {
    }

    public function getFromUser($user_id)
    {
    }

    public function getFromCurrentUser()
    {
    }
}

