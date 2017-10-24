<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use App\Api\v1\Controllers\PinOperationController;
use App\Users;
use App\Places;
use App\Locations;
use App\ChatRooms;
use App\Collections;
use App\Collection_of_pins;
use Auth;
use Validator;
use DB;

use App\Api\v1\Utilities\ErrorCodeUtility;
use App\Api\v1\Utilities\PinUtility;

class CollectionController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function createOne() {
        $this->createOneValidation($this->request);
        $collection = new Collections();
        $collection->user_id = $this->request->self_user_id;
        $collection->name = $this->request->name;
        $collection->description = $this->request->description;
        $collection->type = $this->request->type;
        $collection->is_private = $this->request->is_private == 'true' ? true : false;
        $collection->updateLastUpdatedAt();
        $collection->save();
        return $this->response->created(null, array('collection_id' => $collection->id));
    }

    public function getAll() {
        $user = Users::find($this->request->self_user_id);
        $collections = $user->hasManyCollections;
        $result = array();
        foreach ($collections as $collection) {
            $result[] = $this->collectionFormatting($collection);
        }
        return $this->response->array($result);
    }

    public function updateOne($collection_id) {
        $this->updateOneValidation($this->request);
        $result = PinUtility::validation('collection', $collection_id);
        if($result[0]) {
            return $result[1];
        }
        $collection = $result[1];
        if($collection->user_id != $this->request->self_user_id) {
            return response()->json([
                    'message' => 'You can not update this collection',
                    'error_code' => ErrorCodeUtility::NOT_OWNER_OF_COLLECTION,
                    'status_code' => '403'
                ], 403);
        }
        if($this->request->has('name')) {
            $collection->name = $this->request->name;
        }
        if($this->request->has('description')) {
            $collection->description = $this->request->description;
        }
        if($this->request->has('is_private')){
            $collection->is_private = $this->request->is_private == 'true' ? true : false;
        }
        $collection->updateLastUpdatedAt();
        $collection->save();
        return $this->response->created();
    }

    public function deleteOne($collection_id) {
        $result = PinUtility::validation('collection', $collection_id);
        if($result[0]) {
            return $result[1];
        }
        $collection = $result[1];
        if($collection->user_id != $this->request->self_user_id) {
            return response()->json([
                    'message' => 'You can not delete this collection',
                    'error_code' => ErrorCodeUtility::NOT_OWNER_OF_COLLECTION,
                    'status_code' => '403'
                ], 403);
        }
        $collection_of_pins = Collection_of_pins::where('collection_id', $collection->id)->get();
        $pinOperation = new PinOperationController($this->request);
        foreach ($collection_of_pins as $collection_of_pin) {
            $pinOperation->unsave($collection_id, $collection_of_pin->type, $collection_of_pin->pin_id);
        }
        $collection->delete();
        return $this->response->noContent();
    }

    public function getOne($collection_id) {
        $result = PinUtility::validation('collection', $collection_id);
        if($result[0]) {
            return $result[1];
        }
        $collection = $result[1];
        $result = $this->collectionFormatting($collection);
        if(is_null($result)) {
            return response()->json([
                    'message' => "it's a private collection",
                    'error_code' => ErrorCodeUtility::NOT_OWNER_OF_COLLECTION,
                    'status_code' => '403'
                ], 403);
        }
        $pins = DB::select("SELECT pin_id, created_at as added_at FROM collection_of_pins WHERE collection_id = :collection_id",
                            array('collection_id' => $collection_id));
        $result['pins'] = $pins;
        return $this->response->array($result);
    }

    public function save($collection_id, $type, $pin_id) {
        $result = PinUtility::validation('collection', $collection_id);
        if($result[0]) {
            return $result[1];
        }
        $collection = $result[1];
        if($collection->user_id != $this->request->self_user_id) {
            return response()->json([
                    'message' => 'You can not save',
                    'error_code' => ErrorCodeUtility::NOT_OWNER_OF_COLLECTION,
                    'status_code' => '403'
                ], 403);
        }
        if($collection->type != $type) {
            return response()->json([
                'message' => 'wrong type, not '.$collection->type,
                'error_code' => ErrorCodeUtility::WRONG_TYPE,
                'status_code' => '400'
            ], 400);
        }
        $pinOperation = new PinOperationController($this->request);
        return $pinOperation->save($collection_id, $type, $pin_id, $collection);
    }

    public function unsave($collection_id, $type, $pin_id) {
        $result = PinUtility::validation('collection', $collection_id);
        if($result[0]) {
            return $result[1];
        }
        $collection = $result[1];
        if($collection->user_id != $this->request->self_user_id) {
            return response()->json([
                    'message' => 'You can not unsave',
                    'error_code' => ErrorCodeUtility::NOT_OWNER_OF_COLLECTION,
                    'status_code' => '403'
                ], 403);
        }
        if($collection->type != $type) {
            return response()->json([
                'message' => 'wrong type, neither media nor comment',
                'error_code' => ErrorCodeUtility::WRONG_TYPE,
                'status_code' => '400'
            ], 400);
        }
        $pinOperation = new PinOperationController($this->request);
        return $pinOperation->unsave($collection_id, $type, $pin_id, $collection);
    }

    private function collectionFormatting($collection) {
        if($this->request->self_user_id != $collection->user_id && $collection->is_private == true) {
            return null;
        }
        return array("collection_id" => $collection->id, "name" => $collection->name, 
                     "description" => $collection->description, "type" => $collection->type,
                     "is_private" => $collection->is_private, "created_at" => $collection->created_at->format('Y-m-d H:i:s'),
                     "count" => $collection->count, "last_updated_at" => $collection->last_updated_at);
    }

    private function createOneValidation(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'filled|string',
            'type' => 'required|in:location,place',
            'is_private' => 'required|in:true,false'
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not create collection.',$validator->errors());
        }
    }

    private function updateOneValidation(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'filled|required_without_all:description,s_private|string',
            'description' => 'filled|required_without_all:name,is_private|string',
            'is_private' => 'filled|required_without_all:name,description|in:true,false'
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not create collection.',$validator->errors());
        }
    }

    public static function createDefaultCollections($user_id) {
        $collection1 = new Collections();
        $collection1->user_id = $user_id;
        $collection1->name = "Favorite Places";
        $collection1->type = "place";
        $collection1->is_private = false;
        $collection1->updateLastUpdatedAt();
        $collection1->save();
        $collection2 = new Collections();
        $collection2->user_id = $user_id;
        $collection2->name = "Saved Places";
        $collection2->type = "place";
        $collection2->is_private = false;
        $collection2->updateLastUpdatedAt();
        $collection2->save();
        $collection3 = new Collections();
        $collection3->user_id = $user_id;
        $collection3->name = "Places to Go";
        $collection3->type = "place";
        $collection3->is_private = false;
        $collection3->updateLastUpdatedAt();
        $collection3->save();
        $collection4 = new Collections();
        $collection4->user_id = $user_id;
        $collection4->name = "Favorite Locations";
        $collection4->type = "location";
        $collection4->is_private = false;
        $collection4->updateLastUpdatedAt();
        $collection4->save();
        $collection5 = new Collections();
        $collection5->user_id = $user_id;
        $collection5->name = "Saved Locations";
        $collection5->type = "location";
        $collection5->is_private = false;
        $collection5->updateLastUpdatedAt();
        $collection5->save();
    }
}
