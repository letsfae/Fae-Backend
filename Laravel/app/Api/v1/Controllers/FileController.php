<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use App\Api\v1\Interfaces\RefInterface;
use Validator;
use Dingo\Api\Exception\StoreResourceFailedException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Files;

class FileController extends Controller implements RefInterface {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function upload() {
        // validation
    	$input = $this->request->all();
        $validator = Validator::make($input, [
            'type' => 'required|in:image,video',
            'file' => 'mimes:jpeg,jpg,png,gif,m4v,avi,flv,mp4,mov|required|max:10240',
            'description' => 'string',
            'custom_tag ' => 'string'
        ]);
        
        if($validator->fails()){
    		throw new StoreResourceFailedException('Could not upload File.',$validator->errors());
    	}
        
        //get a random and unique name for the file
        $random_name_raw = strtoupper(md5(uniqid(rand(),true).microtime())); 
        $file_name_storage = 
            substr($random_name_raw,0,8) . '-' . 
            substr($random_name_raw,8,4) . '-' . 
            substr($random_name_raw,12,4). '-' . 
            substr($random_name_raw,16,4). '-' . 
            substr($random_name_raw,20);
        
        //store the file in the file system
        $file = $this->request->file;
        Storage::disk('local')->put('files/'.$file_name_storage, File::get($file));
        
        
        $file_name = $file->getClientOriginalName();
        $storagePath  = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix().'files/';
        $hash = hash_file('sha1', $storagePath.$file_name_storage);
        
        $description = null;
        if ($this->request->has('description')){
            $description = ($this->request->description);
        }
        $custom_tag = null;
        if ($this->request->has('custom_tag')){
            $custom_tag = ($this->request->custom_tag);
        }
        
        $type = $this->request->type;
        $mine_type = Storage::mimeType('files/'.$file_name_storage);
        $size = (int)(File::size($file)/1000);
        $self_user_id = $this->request->self_user_id;
        
        $file_data = new Files();
        $file_data->user_id = $self_user_id;
        $file_data->description = $description;
        $file_data->custom_tag = $custom_tag;
        $file_data->type = $type;
        $file_data->mine_type = $mine_type;
        $file_data->size = $size;
        $file_data->hash = $hash;
        $file_data->directory = '';
        $file_data->file_name_storage = $file_name_storage;
        $file_data->file_name = $file_name;
        $file_data->reference_count = 0;
        $file_data->save();
        
        $content = array('file_id' => $file_data->id);
        return $this->response->created(null, $content);
    }

    public function getAttribute($file_id) {
        $file_data = Files::find($file_id);
        if(is_null($file_data)){
            return $this->response->errorNotFound();
        }
        
        $content = array('file_id' => $file_id,
                        'file_name' => $file_data->file_name,
                        'created_at' => $file_data->created_at->format('Y-m-d H:i:s'),
                        'type' => $file_data->type,
                        'mine_type' => $file_data->mine_type,
                        'description' => $file_data->description,
                        'custom_tag' => $file_data->custom_tag);
        return $this->response->array($content);
    }

    public function getData($file_id) {
        $file_data = Files::find($file_id);
        if(is_null($file_data)){
            return $this->response->errorNotFound();
        }
        
        try {
			$file = Storage::disk('local')->get('files/'.$file_data->directory.$file_data->file_name_storage);
            $mimetype = Storage::mimeType('files/'.$file_data->directory.$file_data->file_name_storage);
		} catch(\Exception $e) {
			return $this->response->errorNotFound();
		}
    
		return response($file, 200)->header('Content-Type', $mimetype);
    }

    /**
     * only for controller
     */
    public static function ref($file_id) {
        $file_data = Files::find($file_id);
        if(is_null($file_data)){
            return $this->response->errorNotFound();
        }
        
        $file_data->reference_count++;
        $file_data->save();
    }

    public static function deref($file_id) {
        $file_data = Files::find($file_id);
        if(is_null($file_data)){
            return $this->response->errorNotFound();
        }
        
        $file_data->reference_count--;
        if( $file_data->reference_count == 0 ){
            Storage::delete('files/'.$file_data->directory.$file_data->file_name_storage);
            $file_data->delete();
        }else{
           $file_data->save();
        }
    }

    public static function exists($file_id) {
        
    }
}
