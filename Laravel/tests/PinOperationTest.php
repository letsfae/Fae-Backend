<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\Geometry;
use App\Pin_operations;
use App\Pin_comments;
use App\Users; 
use App\Comments;
use App\Medias;
use App\Faevors;
use App\Tags;
use App\Files;
use App\ChatRooms;
use App\Sessions;

class PinOperationTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    // use DatabaseMigrations;
    /** @test */
    use PostgisTrait;
    public function setUp() {
        parent::setUp();
        $this->domain = Config::get('api.domain'); 
        $this->markTestSkipped(); 
    } 

    public function tearDown() {
        parent::tearDown();
        $this->beforeApplicationDestroyed(function () {
            DB::disconnect();
        });
    }

    // the correct response of the like.
    public function testLike() { 
        $this->markTestSkipped(); 
        //register of the user.
       $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
        $this->seeInDatabase('pin_operations', ['user_id' => 1, 'pin_id' => 1, 'type' => 'media', 'liked' => true, 'interacted' => true]); 
    }

    // the the response when the type is media but the media information does not exist.
    public function testLike2() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'PIN not found',
                 'error_code' => '404-13',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the response when the Pin_operations information exists in the database.
    public function testLike3() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $pin_operations = Pin_operations::create([
            'type' => 'media',
            'pin_id' => 1,
            'user_id' => 1,
            'liked' =>  true,
            'saved' => false,   
        ]);
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'Bad request, you have already liked this pin!',
                 'error_code' => '400-8',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    //test the response when the  format of pin_id is wrong.
    public function testLike4() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/fae/like', [], [], [], $this->transformHeadersToServerVars($server2));
        $result = false;  
        $this->seeJson([
                 'message' => 'pin_id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    //test the response when the type is wrong.
    public function testLike5() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/wrong/1/like', [], [], [], $this->transformHeadersToServerVars($server2));
        $result = false;  
        $this->seeJson([
                 'message' => 'wrong type, neither media nor comment',
                 'error_code' => '400-7',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    //test the response when the  interacted is false and the geolocation in the seession is null.
    public function testLike6() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameter2 = array(
            'email' => 'letsfae2@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp2',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter2, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        // $pin_operations = Pin_operations::create([
        //     'type' => 'media',
        //     'pin_id' => 1,
        //     'user_id' => 1,
        //     'liked' =>  false,
        //     'saved' => false,   
        //     'interacted' => false
        // ]);
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 2,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440,
            'interaction_radius' => 1
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'location not found',
                 'error_code' => '404-7',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    //test the response when the  interacted is false and the geolocation in the seession exists but above the interaction bound.
    public function testLike7() { 
        $this->markTestSkipped(); 
        //register of the user.
       $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameter2 = array(
            'email' => 'letsfae2@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp2',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter2, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $session = Sessions::find(1); 
        //big difference of the geolocation radius;
        $session->location = new Point(20.031958, 118.288125);
        $session->save();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        // $pin_operations = Pin_operations::create([
        //     'type' => 'media',
        //     'pin_id' => 1,
        //     'user_id' => 1,
        //     'liked' =>  false,
        //     'saved' => false,   
        //     'interacted' => false
        // ]);
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 2,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440,
            'interaction_radius' => 1
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'too far away',
                 'error_code' => '403-3',
                 'status_code' => '403', 
        ]); 
        $result = false;
        if ($response->status() == '403') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // test the correct response of the method of unlike when the liked is true.
    public function testUnLike() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $this->refreshApplication(); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440,
            'interaction_radius' => 1
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication(); 
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->seeInDatabase('pin_operations', ['user_id' => 1, 'pin_id' => 1, 'type' => 'media', 'liked' => false]); 
        $this->assertResponseStatus(204); 
    }

    // test the correct response of the method of unlike when the liked is false.
    public function testUnLike2() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440,
            'interaction_radius' => 1
        ]);  
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'Bad request, you have not liked this pin yet!',
                 'error_code' => '400-11',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // test the response when the pin_operations information does not exist in the database.
    public function testUnLike3() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'PIN not found',
                 'error_code' => '404-13',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // test the response when the format of the pin_id is not valid.
    public function testUnLike4() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440,
            'interaction_radius' => 1
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication(); 
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/media/fae/like', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'pin_id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // test the response when the type is wrong.
    public function testUnLike5() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440,
            'interaction_radius' => 1
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication(); 
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/wrong/1/like', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'wrong type, neither media nor comment',
                 'error_code' => '400-7',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // the correct response of the save.
    public function testSave() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server)); 
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/save', [], [], [], $this->transformHeadersToServerVars($server2));  
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
        $this->seeInDatabase('pin_operations', ['user_id' => 1, 'pin_id' => 1, 'type' => 'media', 'saved' => true]); 
    }

    // the the response when the type is media but the media information does not exist.
    public function testSave2() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/save', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'PIN not found',
                 'error_code' => '404-13',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the response when the Pin_operations information exists in the database and the saved is true.
    public function testSave3() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $pin_operations = Pin_operations::create([
            'type' => 'media',
            'pin_id' => 1,
            'user_id' => 1,
            'liked' =>  false,
            'saved' => true,   
        ]);
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/save', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'Bad request, you have already saved this pin!',
                 'error_code' => '400-10',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the response when the format of the pin_id is wrong.
    public function testSave4() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/fae/save', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'pin_id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // test the response when the type is wrong.
    public function testSave5() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/wrong/1/save', [], [], [], $this->transformHeadersToServerVars($server2));  
        $this->seeJson([
                 'message' => 'wrong type, must be media, comment, or place',
                 'error_code' => '400-7',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the correct response of the method of unlike when the saved is true.
    public function testUnSave() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $this->refreshApplication(); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/save', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication(); 
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/media/1/save', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->seeInDatabase('pin_operations', ['user_id' => 1, 'pin_id' => 1, 'type' => 'media', 'saved' => false]);
        $this->assertResponseStatus(204);
    }

    // test the correct response of the method of unsave when the saved is false.
    public function testUnSave2() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $this->refreshApplication();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/save', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication();
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/media/1/save', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'Bad request, you have not saved this pin yet!',
                 'error_code' => '400-9',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the response when the pin_operations information does not exist in the database.
    public function testUnSave3() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/media/1/save', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'PIN not found',
                 'error_code' => '404-13',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the response when the format of the pin_id is wrong.
    public function testUnSave4() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $this->refreshApplication();
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/save', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication();
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/media/fae/save', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'pin_id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // test the response when the type is wrong.
    public function testUnSave5() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $this->refreshApplication();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/save', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication();
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/wrong/1/save', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'wrong type, must be media, comment, or place',
                 'error_code' => '400-7',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // the correct response of the read.
    public function testRead() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/read', [], [], [], $this->transformHeadersToServerVars($server2));
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
        $this->seeInDatabase('pin_operations', ['user_id' => 1, 'pin_id' => 1, 'type' => 'media', 'saved' => false, 'liked' => false, 'interacted' => false]); 
    }
    // the the response when the type is media but the media information does not exist.
    public function testRead2() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/read', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'PIN not found',
                 'error_code' => '404-13',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // test the response when the format of the pin_id is wrong.
    public function testRead3() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/fae/read', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'pin_id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // test the response when the type is wrong.
    public function testRead4() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/wrong/1/read', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'wrong type, neither media nor comment',
                 'error_code' => '400-7',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // test the correct response of the comment.
    public function testComment() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $session = Sessions::find(1); 
        //big difference of the geolocation radius;
        $session->location = new Point(34.2799, -118.2799);
        $session->save();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440,
            'interaction_radius' => 1
        ]); 
        $parameters = array(
            'content' => 'this is the comment pin test.', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent()); 
        $this->seeJson([
                'pin_comment_id' => 1,
        ]);
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
        $this->seeInDatabase('pin_comments', ['user_id' => 1, 'pin_id' => 1, 'type' => 'media', 'content' => 'this is the comment pin test.']);
    }

    // test whether the input format is right or wrong.
    public function testComment2() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2'
        ]); 
        $parameters = array(
            'content' => null, 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent());    
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Could not comment.' && $array2->errors->content[0] == 'The content field is required.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // the the response when the type is media but the media information does not exist.
    public function testComment3() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        $parameters = array(
            'content' => 'this is the comment pin test.', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters, [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'PIN not found',
                 'error_code' => '404-13',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    //test the response when the  format of pin_id is wrong.
    public function testComment4() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        $parameters = array(
            'content' => 'this is the comment pin test.', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/fae/comments', $parameters, [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'pin_id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    //test the response when the type is wrong.
    public function testComment5() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        $parameters = array(
            'content' => 'this is the comment pin test.', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/wrong/1/comments', $parameters, [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'wrong type, neither media nor comment',
                 'error_code' => '400-7',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    //test the response when the  interacted is false and the geolocation in the seession is null.
    public function testComment6() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameter2 = array(
            'email' => 'letsfae2@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp2',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter2, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $pin_operations = Pin_operations::create([
            'type' => 'media',
            'pin_id' => 1,
            'user_id' => 1,
            'liked' =>  false,
            'saved' => false,   
            'interacted' => false
        ]);
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 2,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440,
            'interaction_radius' => 1
        ]);  
        $parameters = array(
            'content' => 'this is the comment pin test.', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'location not found',
                 'error_code' => '404-7',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    //test the response when the  interacted is false and the geolocation in the seession exists but above the interaction bound.
    public function testComment7() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameter2 = array(
            'email' => 'letsfae2@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp2',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter2, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $session = Sessions::find(1); 
        //big difference of the geolocation radius;
        $session->location = new Point(20.031958, 118.288125);
        $session->save();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $pin_operations = Pin_operations::create([
            'type' => 'media',
            'pin_id' => 1,
            'user_id' => 1,
            'liked' =>  false,
            'saved' => false,   
            'interacted' => false
        ]);
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 2,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440,
            'interaction_radius' => 1
        ]); 
        $parameters = array(
            'content' => 'this is the comment pin test.', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters, [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'too far away',
                 'error_code' => '403-3',
                 'status_code' => '403', 
        ]); 
        $result = false;
        if ($response->status() == '403') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the correct response of the unComment.
    public function testUnComment() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $parameters = array(
            'content' => 'this is the comment pin test.', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();
        $response2 = $this->call('delete', 'http://'.$this->domain.'/pins/comments/1', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->notseeInDatabase('pin_comments', ['user_id' => 1, 'pin_id' => 1, 'type' => 'media']); 
        $this->assertResponseStatus(204);
    }

    // test the response when the pin_comments information does not exist in the database.
    public function testUnComment2() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/comments/1', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'comment not found',
                 'error_code' => '404-9',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the response when the pin_comments with the user_id is not the same as the login user_id.
    public function testUnComment3() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameter2 = array(
            'email' => 'letsfae2@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp2',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter2, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $comments = Comments::create([
            'user_id' => 2, 
            'content' => 'This is the test', 
            'geolocation' => new Point(34.031958,-118.288125),  
            'duration' => 1440
        ]);

        $parameters = array(
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        $this->refreshApplication(); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 2,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $parameter = array(
            'content' => 'this is the comment pin test.', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameter, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication(); 
        $parameters2 = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters2, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server3 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/comments/1', [], [], [], $this->transformHeadersToServerVars($server3)); 
        $this->seeJson([
                 'message' => 'You can not delete this comment',
                 'error_code' => '403-2',
                 'status_code' => '403', 
        ]); 
        $result = false;
        if ($response->status() == '403') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the response when the format of pin_comment_id is wrong.
    public function testUnComment4() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/comments/fae', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'pin_comment_id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the correct response of the getPinAttribute.
    public function testGetPinAttribute() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $parameter = array(
            'content' => 'this is the comment pin test.', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameter, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication(); 
        $response2 = $this->call('post', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2));  
        $response = $this->call('get', 'http://'.$this->domain.'/pins/media/1/attribute', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                'type' => 'media',
                'pin_id' => 1,
                'likes' => 1,
                'saves' => 0,
                'comments' => 1 
        ]);
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
    }

    // test the response when the type is media but the media information does not exist.
    public function testGetPinAttribute2() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();  
        $response = $this->call('get', 'http://'.$this->domain.'/pins/media/1/attribute', [], [], [], $this->transformHeadersToServerVars($server2));  
        $this->seeJson([
                 'message' => 'PIN not found',
                 'error_code' => '404-13',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    // test the response when the format of pin_id is wrong.
    public function testGetPinAttribute3() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $parameter = array(
            'content' => 'this is the comment pin test.', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameter, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication(); 
        $response2 = $this->call('post', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2));  
        $response = $this->call('get', 'http://'.$this->domain.'/pins/media/fae/attribute', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'pin_id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }
    // test the response when the type is wrong.
    public function testGetPinAttribute4() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $parameter = array(
            'content' => 'this is the comment pin test.', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameter, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication(); 
        $response2 = $this->call('post', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2));  
        $response = $this->call('get', 'http://'.$this->domain.'/pins/wrong/1/attribute', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'wrong type, neither media nor comment',
                 'error_code' => '400-7',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }
    // test the correct response of the getPinCommentList.
    public function testGetPinCommentList() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();

        $media = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]);  
        for ($i = 1; $i < 32; $i++) {
            ${'pinComments' . $i} = Pin_comments::create([
                'user_id' => 1,
                'type' => 'media',
                'pin_id' => 1,
                'content' => 'This is the pinComments'.$i 
            ]);   
        }
        $parameters2 = array(
             'vote' => 'up',
        ); 
        for ($i = 1; $i < 32; $i++) {
            ${'vote_response'.$i} = $this->call('post', 'http://'.$this->domain.'/pins/comments/'.$i.'/vote', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        }
        $content = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        );
        // get the pinComments of the page 1.
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/pins/media/1/comments', $content, [], [], $this->transformHeadersToServerVars($server2));
        // var_dump($response_page1);
        $array2 = json_decode($response_page1->getContent());  
        for ($i = 0; $i < 30; $i++) {
            $this->seeJson([  
                        'pin_comment_id' => $i + 1,
                        'user_id' => 1,
                        'anonymous' => false,
                        'nick_name' => null,
                        'content' => 'This is the pinComments'.($i + 1), 
                        'pin_comment_operations' => array(
                            'vote' => 'up',
                            'vote_timestamp' => $array2[$i]->pin_comment_operations->vote_timestamp, 
                        ),
                        'vote_up_count' => 1,
                        'vote_down_count' => 0,
                        'created_at' => $array2[$i]->created_at, 
            ]);
        }
        $this->refreshApplication();
        $content2 = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 2,
        );
        // get the pinComments of the page 2.
        $response_page2 = $this->call('get', 'http://'.$this->domain.'/pins/media/1/comments', $content2, [], [], $this->transformHeadersToServerVars($server2)); 
        $array3 = json_decode($response_page2->getContent()); 
        $this->seeJson([  
                    'pin_comment_id' => 31,
                    'user_id' => 1,
                    'anonymous' => false,
                    'nick_name' => null,
                    'content' => 'This is the pinComments31', 
                    'pin_comment_operations' => array(
                        'vote' => 'up',
                        'vote_timestamp' => $array3[0]->pin_comment_operations->vote_timestamp, 
                    ),
                    'vote_up_count' => 1,
                    'vote_down_count' => 0,
                    'created_at' => $array3[0]->created_at, 
        ]);
        $result = false;
        if ($response_page1->headers->get('page') == '1' && $response_page1->headers->get('total-pages') == '2' && $response_page1->status() == '200') {
            $result = true;
        } 
        $this->assertEquals(true, $result);
    }

    // test the response when the type is media but the media information does not exist.
    public function testGetPinCommentList2() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );     
        for ($i = 1; $i < 32; $i++) {
            ${'pinComments' . $i} = Pin_comments::create([
                'user_id' => 1,
                'type' => 'media',
                'pin_id' => 1,
                'content' => 'This is the pinComments'.$i 
            ]);   
        }
        $content = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        );
        // get the pinComments of the page 1.
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/pins/media/1/comments', $content, [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'PIN not found',
                 'error_code' => '404-13',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response_page1->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    // test the select page is larger than the total page.
    public function testGetPinCommentList3() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();

        $media = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
             'duration' => 1440
        ]);  
        for ($i = 1; $i < 31; $i++) {
            ${'pinComments' . $i} = Pin_comments::create([
                'user_id' => 1,
                'type' => 'media',
                'pin_id' => 1,
                'content' => 'This is the pinComments'.$i 
            ]);   
        }
        $content = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 2,
        );
        // get the pinComments of the page 1.
        $response = $this->call('get', 'http://'.$this->domain.'/pins/media/1/comments', $content, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent()); 
        $this->seeJson([]);
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test whether the format of the input is valid.
    public function testGetPinCommentList4() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();

        $media = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]);  
        for ($i = 1; $i < 32; $i++) {
            ${'pinComments' . $i} = Pin_comments::create([
                'user_id' => 1,
                'type' => 'media',
                'pin_id' => 1,
                'content' => 'This is the pinComments'.$i 
            ]);   
        }
        //wrong format of start_time.
        $content = array(
            'start_time' => '2016-06-08 21:22',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        );
         $this->refreshApplication();
        // get the pinComments of the page 1. 
        $response = $this->call('get', 'http://'.$this->domain.'/pins/media/1/comments', $content, [], [], $this->transformHeadersToServerVars($server2));  
        $array2 = json_decode($response->getContent());  
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Could not get user comments.' && $array2->errors->start_time[0] == 'The start time does not match the format Y-m-d H:i:s.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // test the correct response when the pin_id is not integer.
    public function testGetPinCommentList5() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();

        $media = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]);  
        for ($i = 1; $i < 32; $i++) {
            ${'pinComments' . $i} = Pin_comments::create([
                'user_id' => 1,
                'type' => 'media',
                'pin_id' => 1,
                'content' => 'This is the pinComments'.$i 
            ]);   
        }
        $parameters2 = array(
             'vote' => 'up',
        ); 
        for ($i = 1; $i < 32; $i++) {
            ${'vote_response'.$i} = $this->call('post', 'http://'.$this->domain.'/pins/comments/'.$i.'/vote', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        }
        $content = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        );
        // get the pinComments of the page 1.
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/pins/media/fae/comments', $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'pin_id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response_page1->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }
    // test the correct response when the type is wrong.
    public function testGetPinCommentList6() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();

        $media = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]);  
        for ($i = 1; $i < 32; $i++) {
            ${'pinComments' . $i} = Pin_comments::create([
                'user_id' => 1,
                'type' => 'media',
                'pin_id' => 1,
                'content' => 'This is the pinComments'.$i 
            ]);   
        }
        $parameters2 = array(
             'vote' => 'up',
        ); 
        for ($i = 1; $i < 32; $i++) {
            ${'vote_response'.$i} = $this->call('post', 'http://'.$this->domain.'/pins/comments/'.$i.'/vote', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        }
        $content = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        );
        // get the pinComments of the page 1.
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/pins/wrong/1/comments', $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'wrong type, neither media nor comment',
                 'error_code' => '400-7',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response_page1->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }
    // test the correct response of the method of getUserPinList of the given user when the request user_id is not the same as the logged in user_id.
    public function testGetUserPinList() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   


        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();

        $tags = new Tags;
        $tags->title = 'fae';
        $tags->color = '#fff000';
        $tags->user_id = 1;  
        $tags->reference_count = 0;
        $tags->save();

        $tags = new Tags;
        $tags->title = 'fae1';
        $tags->color = '#fff000';
        $tags->user_id = 1;  
        $tags->reference_count = 0;
        $tags->save();
            
        $this->refreshApplication(); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $parameters = array();
        $response = array();
        for ($i = 0; $i < 31; $i++) {
            $parameters[$i] = array(
                'file_ids' => '1;2',
                'tag_ids' => '1;2',
                'description' => 'this is the test'.$i,
                'geo_latitude' => '-89.99',
                'geo_longitude' => '-118.2799',
                'duration' => '1440',
                'interaction_radius' => '100', 
                ); 
        }
        //create the medias.
        for ($i = 0; $i < 31; $i++) {
            $response[$i] = $this->call('post', 'http://'.$this->domain.'/medias', $parameters[$i], [], [], $this->transformHeadersToServerVars($server2));   
            sleep(1);
            $this->refreshApplication();
            $response_like = $this->call('post', 'http://'.$this->domain.'/pins/media/'.($i + 1).'/like', [], [], [], $this->transformHeadersToServerVars($server2));
            $this->refreshApplication();
            //post save pin_operations
            $response_save = $this->call('post', 'http://'.$this->domain.'/pins/media/'.($i + 1).'/save', [], [], [], $this->transformHeadersToServerVars($server2)); 
            $this->refreshApplication();
            $parameters1 = array(
                'content' => 'This is the pin comment test', 
             );  
            //post comment pin_operations
            $response_comment = $this->call('post', 'http://'.$this->domain.'/pins/media/'.($i + 1).'/comments', $parameters1, [], [], $this->transformHeadersToServerVars($server2));  
            $this->refreshApplication();  
        }  
        $parameter2 = array(
            'email' => 'letsfae2@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp2',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ); 
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter2, [], [], $this->transformHeadersToServerVars($server)); 
        $this->refreshApplication();
        $parameter3 = array(
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $login_response2 = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter3, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $array_2 = json_decode($login_response2->getContent());
        $server3 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array_2->debug_base64ed,
        );
         
        $content = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        ); 
        // get the pinComments of the page 1.
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/pins/users/1', $content, [], [], $this->transformHeadersToServerVars($server3));
        $array2 = json_decode($response_page1->getContent());  
        for ($i = 0; $i < 30; $i++) {
            $this->seeJson([  
                'pin_id' => $array2[$i]->pin_id, 
                'type' => 'media', 
                'created_at' => $array2[$i]->created_at, 
                'pin_object' => array(
                    'media_id' => $array2[$i]->pin_object->media_id,
                    'user_id' => 1, 
                    'nick_name' => null,
                    'anonymous' => false,
                    'file_ids' =>  array('1','2'),
                    'tag_ids' =>  array('1','2'),
                    'description' => 'this is the test'.(30 - $i),
                    'geolocation' => array( 
                        'latitude' => -89.99,
                        'longitude' => -118.2799,
                    ),
                    'liked_count' => 1,
                    'saved_count' => 1,
                    'comment_count' => 1, 
                    'feeling_count' => [0,0,0,0,0,0,0,0,0,0,0],
                    'created_at' => $array2[$i]->pin_object->created_at, 
                    'user_pin_operations' => array(
                        'is_liked' => false,
                        'liked_timestamp' => null,
                        'is_saved' => false,
                        'saved_timestamp' => null,
                        'is_read' => false,
                        'read_timestamp' => null,
                    ),
                ),
            ]);
        }
        $this->refreshApplication();
        $content2 = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 2,
        );
        // get the pinComments of the page 2.
        $response_page2 = $this->call('get', 'http://'.$this->domain.'/pins/users/1', $content2, [], [], $this->transformHeadersToServerVars($server3)); 
        $array3 = json_decode($response_page2->getContent()); 
        $this->seeJson([  
                'pin_id' => $array3[0]->pin_id, 
                'type' => $array3[0]->type, 
                'created_at' => $array3[0]->created_at, 
                'pin_object' => array(
                    'media_id' => $array3[0]->pin_object->media_id,
                    'user_id' => 1, 
                    'nick_name' => null,
                    'anonymous' => false,
                    'file_ids' => array('1','2'),
                    'tag_ids' => array('1','2'),
                    'description' => 'this is the test0',
                    'geolocation' => array( 
                        'latitude' => $array3[0]->pin_object->geolocation->latitude,
                        'longitude' => $array3[0]->pin_object->geolocation->longitude,
                    ),
                    'liked_count' => 1,
                    'saved_count' => 1,
                    'comment_count' => 1, 
                    'feeling_count' => [0,0,0,0,0,0,0,0,0,0,0],
                    'created_at' => $array3[0]->pin_object->created_at, 
                    'user_pin_operations' => array(
                        'is_liked' => false,
                        'liked_timestamp' => $array3[0]->pin_object->user_pin_operations->liked_timestamp,
                        'is_saved' => false,
                        'saved_timestamp' => $array3[0]->pin_object->user_pin_operations->saved_timestamp,
                        'is_read' => false,
                        'read_timestamp' => $array3[0]->pin_object->user_pin_operations->read_timestamp,
                    ),
                )
         ]);
        $result = false;
        if ($response_page1->headers->get('page') == '1' && $response_page1->headers->get('total-pages') == '2' && $response_page1->status() == '200') {
            $result = true;
        } 
        $this->assertEquals(true, $result);
    }

    //test whether the user with user_id exists.
    public function testGetUserPinList2() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();

        $media = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
             'duration' => 1440
        ]);  
        for ($i = 1; $i < 32; $i++) {
            ${'pinComments' . $i} = Pin_comments::create([
                'user_id' => 1,
                'type' => 'media',
                'pin_id' => 1,
                'content' => 'This is the pinComments'.$i 
            ]);   
        }
        $content = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        );
        // no such user exists
        $response = $this->call('get', 'http://'.$this->domain.'/pins/users/3', $content, [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'user not found',
                 'error_code' => '404-3',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test whether the format of the user_id is right. 
    public function testGetUserPinList3() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();

        $media = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
             'duration' => 1440
        ]);  
        for ($i = 1; $i < 32; $i++) {
            ${'pinComments' . $i} = Pin_comments::create([
                'user_id' => 1,
                'type' => 'media',
                'pin_id' => 1,
                'content' => 'This is the pinComments'.$i 
            ]);   
        }
        $content = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        );
        //the format of the user_id is not valid and the user does not exist.
        $response = $this->call('get', 'http://'.$this->domain.'/pins/users/letsfae', $content, [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'user_id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    // test the select page is larger than the total page.
    public function testGetUserPinList4() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();

        $media = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
             'duration' => 1440
        ]);  
        for ($i = 1; $i < 31; $i++) {
            ${'pinComments' . $i} = Pin_comments::create([
                'user_id' => 1,
                'type' => 'media',
                'pin_id' => 1,
                'content' => 'This is the pinComments'.$i 
            ]);   
        }
        $content = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 2,
        );
        // get the pinComments of the page 2.
        $response = $this->call('get', 'http://'.$this->domain.'/pins/users/1', $content, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent()); 
        $this->seeJson([]);
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test whether the format of the input is valid.
    public function testGetUserPinList5() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();

        $media = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
             'duration' => 1440
        ]);  
        for ($i = 1; $i < 32; $i++) {
            ${'pinComments' . $i} = Pin_comments::create([
                'user_id' => 1,
                'type' => 'media',
                'pin_id' => 1,
                'content' => 'This is the pinComments'.$i 
            ]);   
        }
        //wrong format of start_time.
        $content = array(
            'start_time' => '2016-06-08 21:22',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        );
        // get the pinComments of the page 1.
        $response = $this->call('get', 'http://'.$this->domain.'/pins/users/1', $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent());  
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Could not get user pin list.' && $array2->errors->start_time[0] == 'The start time does not match the format Y-m-d H:i:s.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // test the correct response of the getSelfPinList.
    public function testGetSelfPinList() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $this->refreshApplication(); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();

       $parameters = array();
       $response = array();
        for ($i = 0; $i < 31; $i++) {
            $parameters[$i] = array(
                'file_ids' => '1;2', 
                'description' => 'this is the test'.$i,
                'geo_latitude' => '-89.99',
                'geo_longitude' => '-118.2799',
                'duration' => '1440',
                'interaction_radius' => '100', 
                ); 
        }
        //create the medias.
        for ($i = 0; $i < 31; $i++) {
            $response[$i] = $this->call('post', 'http://'.$this->domain.'/medias', $parameters[$i], [], [],$this->transformHeadersToServerVars($server2));  
            sleep(1); 
            $this->refreshApplication();
            $parameter2 = array(
                'content' => 'This is the pin comment test', 
             );  
            //post comment pin_operations
            $response_comment = $this->call('post', 'http://'.$this->domain.'/pins/media/'.($i + 1).'/comments', $parameter2, [], [],$this->transformHeadersToServerVars($server2));  
            $this->refreshApplication();  
        }   
        $content = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        );
        // get the pinComments of the page 1.
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/pins/users', $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response_page1->getContent()); 
        for ($i = 0; $i < 30; $i++) {
            $this->seeJson([  
                'pin_id' => $array2[$i]->pin_id, 
                'type' => 'media', 
                'created_at' => $array2[$i]->created_at, 
                'pin_object' => array(
                    'media_id' => $array2[$i]->pin_object->media_id,
                    'user_id' => 1, 
                    'nick_name' => null,
                    'anonymous' => false,
                    'file_ids' =>  array('1','2'),
                    'tag_ids' =>  null,
                    'description' => 'this is the test'.(30 - $i),
                    'geolocation' => array( 
                        'latitude' => -89.99,
                        'longitude' => -118.2799,
                    ),
                    'liked_count' => 0,
                    'saved_count' => 0,
                    'comment_count' => 1, 
                    'feeling_count' => [0,0,0,0,0,0,0,0,0,0,0],
                    'created_at' => $array2[$i]->pin_object->created_at, 
                    'user_pin_operations' => array(
                        'is_liked' => false,
                        'liked_timestamp' => null,
                        'is_saved' => false,
                        'saved_timestamp' => null,
                        'feeling' => -1,
                        'feeling_timestamp' => null,
                        'is_read' => true,
                        'read_timestamp' => $array2[$i]->pin_object->user_pin_operations->read_timestamp,
                    ),
                ),
            ]);
        }
        $this->refreshApplication();
        $content2 = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 2,
        );
        // get the pinComments of the page 2.
        $response_page2 = $this->call('get', 'http://'.$this->domain.'/pins/users', $content2, [], [], $this->transformHeadersToServerVars($server2)); 
        $array3 = json_decode($response_page2->getContent()); 
        $this->seeJson([  
            'pin_id' => $array3[0]->pin_id, 
            'type' => 'media', 
            'created_at' => $array3[0]->created_at, 
            'pin_object' => array(
                'media_id' => $array3[0]->pin_object->media_id,
                'user_id' => 1, 
                'nick_name' => null,
                'anonymous' => false,
                'file_ids' =>  array('1','2'),
                'tag_ids' =>  null,
                'description' => 'this is the test0',
                'geolocation' => array( 
                    'latitude' => -89.99,
                    'longitude' => -118.2799,
                ),
                'liked_count' => 0,
                'saved_count' => 0,
                'comment_count' => 1, 
                'feeling_count' => [0,0,0,0,0,0,0,0,0,0,0],
                'created_at' => $array3[0]->pin_object->created_at, 
                'user_pin_operations' => array(
                    'is_liked' => false,
                    'liked_timestamp' => null,
                    'is_saved' => false,
                    'saved_timestamp' => null,
                    'feeling' => -1,
                    'feeling_timestamp' => null,
                    'is_read' => true,
                    'read_timestamp' => $array3[0]->pin_object->user_pin_operations->read_timestamp,
                ),
            ),
        ]);
        $result = false;
        if ($response_page1->headers->get('page') == '1' && $response_page1->headers->get('total-pages') == '2' && $response_page1->status() == '200') {
            $result = true;
        } 
        $this->assertEquals(true, $result);
    }
 
    // test the correct response of the getSavedPinList when the type is not place.
    public function testGetSavedPinList() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $this->refreshApplication();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();

        $parameters = array();
        $responses = array();
        for ($i = 0; $i < 31; $i++) {
            $parameters[$i] = array(
                'file_ids' => '1;2', 
                'description' => 'this is the test'.$i,
                'geo_latitude' => '-89.99',
                'geo_longitude' => '-118.2799',
                'duration' => '1440',
                'interaction_radius' => '100', 
            ); 
        }
        //create the medias.
        for ($i = 0; $i < 31; $i++) {
            $responses[$i] = $this->call('post', 'http://'.$this->domain.'/medias', $parameters[$i], [], [],$this->transformHeadersToServerVars($server2));  
            sleep(1); 
            $this->refreshApplication(); 
            $response = $this->call('post', 'http://'.$this->domain.'/pins/media/'.($i+1).'/save', [], [], [], $this->transformHeadersToServerVars($server2));
            $this->refreshApplication();
        }    
        $content = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        );
        //get the pinComments of the page 1.
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/pins/saved', $content, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response_page1->getContent());  
        for ($i = 0; $i < 30; $i++) {
            $this->seeJson([  
                'pin_id' => $array2[$i]->pin_id, 
                'type' => 'media', 
                'created_at' => $array2[$i]->created_at, 
                'pin_object' => array(
                    'media_id' => $array2[$i]->pin_object->media_id,
                    'user_id' => 1, 
                    'nick_name' => null,
                    'anonymous' => false,
                    'file_ids' =>  array('1','2'),
                    'tag_ids' =>  null,
                    'description' => 'this is the test'.(30 - $i),
                    'geolocation' => array( 
                        'latitude' => -89.99,
                        'longitude' => -118.2799,
                    ),
                    'liked_count' => 0,
                    'saved_count' => 1,
                    'comment_count' => 0, 
                    'feeling_count' => [0,0,0,0,0,0,0,0,0,0,0],
                    'created_at' => $array2[$i]->pin_object->created_at, 
                    'user_pin_operations' => array(
                        'is_liked' => false,
                        'liked_timestamp' => null,
                        'is_saved' => true,
                        'saved_timestamp' => $array2[$i]->pin_object->user_pin_operations->saved_timestamp,
                        'feeling' => -1,
                        'feeling_timestamp' => null,
                        'is_read' => true,
                        'read_timestamp' => $array2[$i]->pin_object->user_pin_operations->read_timestamp,
                    ),
                ),
            ]);
        }
        $this->refreshApplication();
        $content2 = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 2,
        );
        // get the pinComments of the page 2.
        $response_page2 = $this->call('get', 'http://'.$this->domain.'/pins/saved', $content2, [], [], $this->transformHeadersToServerVars($server2)); 
        $array3 = json_decode($response_page2->getContent()); 
        $this->seeJson([  
            'pin_id' => $array3[0]->pin_id, 
            'type' => 'media', 
            'created_at' => $array3[0]->created_at, 
            'pin_object' => array(
                'media_id' => $array3[0]->pin_object->media_id,
                'user_id' => 1, 
                'nick_name' => null,
                'anonymous' => false,
                'file_ids' =>  array('1','2'),
                'tag_ids' =>  null,
                'description' => 'this is the test0',
                'geolocation' => array( 
                    'latitude' => -89.99,
                    'longitude' => -118.2799,
                ),
                'liked_count' => 0,
                'saved_count' => 1,
                'comment_count' => 0, 
                'feeling_count' => [0,0,0,0,0,0,0,0,0,0,0],
                'created_at' => $array3[0]->pin_object->created_at, 
                'user_pin_operations' => array(
                    'is_liked' => false,
                    'liked_timestamp' => null,
                    'is_saved' => true,
                    'saved_timestamp' => $array3[0]->pin_object->user_pin_operations->saved_timestamp,
                    'feeling' => -1,
                    'feeling_timestamp' => null,
                    'is_read' => true,
                    'read_timestamp' => $array3[0]->pin_object->user_pin_operations->read_timestamp,
                ),
            ),
        ]);
        $result = false;
        if ($response_page1->headers->get('page') == '1' && $response_page1->headers->get('total-pages') == '2' && $response_page1->status() == '200') {
            $result = true;
        } 
        $this->assertEquals(true, $result);
    }
    // test the response when the input format is wrong.
    public function testGetSavedPinList2() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $this->refreshApplication();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();

        $parameters = array();
        $responses = array();
        for ($i = 0; $i < 31; $i++) {
            $parameters[$i] = array(
                'file_ids' => '1;2', 
                'description' => 'this is the test'.$i,
                'geo_latitude' => '-89.99',
                'geo_longitude' => '-118.2799',
                'duration' => '1440',
                'interaction_radius' => '100', 
            ); 
        }
        //create the medias.
        for ($i = 0; $i < 31; $i++) {
            $responses[$i] = $this->call('post', 'http://'.$this->domain.'/medias', $parameters[$i], [], [],$this->transformHeadersToServerVars($server2));  
            sleep(1); 
            $this->refreshApplication(); 
            $response = $this->call('post', 'http://'.$this->domain.'/pins/media/'.($i+1).'/save', [], [], [], $this->transformHeadersToServerVars($server2));
            $this->refreshApplication();
        }    
        $content = array(
            'start_time' => '2016-06-08 21:22',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        );
        //get the pinComments of the page 1.
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/pins/saved', $content, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response_page1->getContent()); 
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Could not get user comments.' && $array2->errors->start_time[0] == 'The start time does not match the format Y-m-d H:i:s.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // test the correct response of the getSavedPinList when the type is place.
    // public function testGetSavedPinList3() { 
    //     $this->markTestSkipped(); 
    //     //register of the user.
    //     $parameter1 = array(
    //         'email' => 'letsfae@126.com',
    //         'password' => 'letsfaego',
    //         'first_name' => 'kevin',
    //         'last_name' => 'zhang',
    //         'user_name' => 'faeapp',
    //         'gender' => 'male',
    //         'birthday' => '1992-02-02',
    //     );
    //     $server = array(
    //         'Accept' => 'application/x.faeapp.v1+json', 
    //         'Fae-Client-Version' => 'ios-0.0.1',
    //     );
    //     $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
    //     $this->refreshApplication();  
    //     $parameters = array(
    //         'email' => 'letsfae@126.com', 
    //         'password' => 'letsfaego',
    //         'user_name' => 'faeapp',
    //     );
    //     $server = array(
    //         'Accept' => 'application/x.faeapp.v1+json', 
    //         'Fae-Client-Version' => 'ios-0.0.1', 
    //     );
    //     //login of the user.
    //     $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
    //     $array = json_decode($login_response->getContent());
    //     $this->refreshApplication();
    //     $server2 = array(
    //         'Accept' => 'application/x.faeapp.v1+json', 
    //         'Fae-Client-Version' => 'ios-0.0.1', 
    //         'Authorization' => 'FAE '.$array->debug_base64ed,
    //     );   

    //     $files = new Files;
    //     $files->user_id = 1;
    //     $files->type = 'video';
    //     $files->mine_type = 'video';
    //     $files->size = 256;
    //     $files->hash = 'test';
    //     $files->directory = 'test';
    //     $files->file_name_storage = 'test';
    //     $files->file_name = 'test';
    //     $files->reference_count = 0;
    //     $files->save();

    //     $files = new Files;
    //     $files->user_id = 1;
    //     $files->type = 'image';
    //     $files->mine_type = 'image';
    //     $files->size = 256;
    //     $files->hash = 'test1';
    //     $files->directory = 'test1';
    //     $files->file_name_storage = 'test1';
    //     $files->file_name = 'test1';
    //     $files->reference_count = 0;
    //     $files->save();

    //     $parameters = array();
    //     $responses = array();
    //     for ($i = 0; $i < 31; $i++) {
    //         $parameters[$i] = array(
    //             'file_ids' => '1;2', 
    //             'description' => 'this is the test'.$i,
    //             'geo_latitude' => '-89.99',
    //             'geo_longitude' => '-118.2799',
    //             'duration' => '1440',
    //             'interaction_radius' => '100', 
    //         ); 
    //     }
    //     //create the medias.
    //     for ($i = 0; $i < 31; $i++) {
    //         $responses[$i] = $this->call('post', 'http://'.$this->domain.'/medias', $parameters[$i], [], [],$this->transformHeadersToServerVars($server2));  
    //         sleep(1); 
    //         $this->refreshApplication(); 
    //         $response = $this->call('post', 'http://'.$this->domain.'/pins/media/'.($i+1).'/save', [], [], [], $this->transformHeadersToServerVars($server2));
    //         $this->refreshApplication();
    //     }    
    //     $content = array(
    //         'start_time' => '2016-06-08 21:22:39',
    //         'end_time' => date("Y-m-d H:i:s"),
    //         'page' => 1,
    //     );
    //     //get the pinComments of the page 1.
    //     $response_page1 = $this->call('get', 'http://'.$this->domain.'/pins/saved', $content, [], [], $this->transformHeadersToServerVars($server2));
    //     $array2 = json_decode($response_page1->getContent());  
    //     for ($i = 0; $i < 30; $i++) {
    //         $this->seeJson([  
    //             'pin_id' => $array2[$i]->pin_id, 
    //             'type' => 'media', 
    //             'created_at' => $array2[$i]->created_at, 
    //             'pin_object' => array(
    //                 'media_id' => $array2[$i]->pin_object->media_id,
    //                 'user_id' => 1, 
    //                 'nick_name' => null,
    //                 'anonymous' => false,
    //                 'file_ids' =>  array('1','2'),
    //                 'tag_ids' =>  null,
    //                 'description' => 'this is the test'.(30 - $i),
    //                 'geolocation' => array( 
    //                     'latitude' => -89.99,
    //                     'longitude' => -118.2799,
    //                 ),
    //                 'liked_count' => 0,
    //                 'saved_count' => 1,
    //                 'comment_count' => 0, 
    //                 'feeling_count' => [0,0,0,0,0,0,0,0,0,0,0],
    //                 'created_at' => $array2[$i]->pin_object->created_at, 
    //                 'user_pin_operations' => array(
    //                     'is_liked' => false,
    //                     'liked_timestamp' => null,
    //                     'is_saved' => true,
    //                     'saved_timestamp' => $array2[$i]->pin_object->user_pin_operations->saved_timestamp,
    //                     'feeling' => -1,
    //                     'feeling_timestamp' => null,
    //                     'is_read' => true,
    //                     'read_timestamp' => $array2[$i]->pin_object->user_pin_operations->read_timestamp,
    //                 ),
    //             ),
    //         ]);
    //     }
    //     $this->refreshApplication();
    //     $content2 = array(
    //         'start_time' => '2016-06-08 21:22:39',
    //         'end_time' => date("Y-m-d H:i:s"),
    //         'page' => 2,
    //     );
    //     // get the pinComments of the page 2.
    //     $response_page2 = $this->call('get', 'http://'.$this->domain.'/pins/saved', $content2, [], [], $this->transformHeadersToServerVars($server2)); 
    //     $array3 = json_decode($response_page2->getContent()); 
    //     $this->seeJson([  
    //         'pin_id' => $array3[0]->pin_id, 
    //         'type' => 'media', 
    //         'created_at' => $array3[0]->created_at, 
    //         'pin_object' => array(
    //             'media_id' => $array3[0]->pin_object->media_id,
    //             'user_id' => 1, 
    //             'nick_name' => null,
    //             'anonymous' => false,
    //             'file_ids' =>  array('1','2'),
    //             'tag_ids' =>  null,
    //             'description' => 'this is the test0',
    //             'geolocation' => array( 
    //                 'latitude' => -89.99,
    //                 'longitude' => -118.2799,
    //             ),
    //             'liked_count' => 0,
    //             'saved_count' => 1,
    //             'comment_count' => 0, 
    //             'feeling_count' => [0,0,0,0,0,0,0,0,0,0,0],
    //             'created_at' => $array3[0]->pin_object->created_at, 
    //             'user_pin_operations' => array(
    //                 'is_liked' => false,
    //                 'liked_timestamp' => null,
    //                 'is_saved' => true,
    //                 'saved_timestamp' => $array3[0]->pin_object->user_pin_operations->saved_timestamp,
    //                 'feeling' => -1,
    //                 'feeling_timestamp' => null,
    //                 'is_read' => true,
    //                 'read_timestamp' => $array3[0]->pin_object->user_pin_operations->read_timestamp,
    //             ),
    //         ),
    //     ]);
    //     $result = false;
    //     if ($response_page1->headers->get('page') == '1' && $response_page1->headers->get('total-pages') == '2' && $response_page1->status() == '200') {
    //         $result = true;
    //     } 
    //     $this->assertEquals(true, $result);
    // }

    // test the correct response of the vote.
    public function testVote() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 
        $parameters2 = array(
            'file_ids' => '1;2', 
            'description' => 'this is the test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
            'duration' => '1440',
            'interaction_radius' => '100', 
        );  
        //create the medias. 
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();
        $parameters3 = array(
            'content' => 'This is the pin comment test', 
        );  
        //post comment pin_operations
        $response_comment = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters3, [], [], $this->transformHeadersToServerVars($server2));  
        $this->refreshApplication();  
        $content = array(
            'vote' => 'up', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/comments/1/vote', $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent());  
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
        $this->seeInDatabase('pin_comments', ['user_id' => 1, 'pin_id' => 1, 'type' => 'media', 'content' => 'This is the pin comment test', 'vote_up_count' => 1, 'vote_down_count' => 0]);
        $this->seeInDatabase('pin_comment_operations', ['user_id' => 1, 'pin_comment_id' => 1, 'vote' => 1]);
    }
    // test the response when the input format is wrong.
    public function testVote2() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 
        $parameters2 = array(
            'file_ids' => '1;2', 
            'description' => 'this is the test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
            'duration' => '1440',
            'interaction_radius' => '100', 
        );  
        //create the medias. 
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();
        $parameters3 = array(
            'content' => 'This is the pin comment test', 
        );  
        //post comment pin_operations
        $response_comment = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters3, [], [], $this->transformHeadersToServerVars($server2));  
        $this->refreshApplication(); 
        $content = array(
            'vote' => 'wrong format', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/comments/1/vote', $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent()); 
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Could not vote this comment.' && $array2->errors->vote[0] == 'The selected vote is invalid.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // test the response when the pin_comment_id is not valid.
    public function testVote3() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 
        $parameters2 = array(
            'file_ids' => '1;2', 
            'description' => 'this is the test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
            'duration' => '1440',
            'interaction_radius' => '100', 
        );  
        //create the medias. 
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();
        $parameters3 = array(
            'content' => 'This is the pin comment test', 
        );  
        //post comment pin_operations
        $response_comment = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters3, [], [], $this->transformHeadersToServerVars($server2));  
        $this->refreshApplication(); 
        $content = array(
            'vote' => 'up', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/comments/faeapp/vote', $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'pin_comment_id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the response when the pin_comment is null.
    public function testVote4() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 
        $parameters2 = array(
            'file_ids' => '1;2', 
            'description' => 'this is the test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
            'duration' => '1440',
            'interaction_radius' => '100', 
        );  
        //create the medias. 
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters2, [], [], $this->transformHeadersToServerVars($server2));  
        $content = array(
            'vote' => 'up', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/comments/1/vote', $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'comment not found',
                 'error_code' => '404-9',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the response when the pin_comment is not null and the vote in PinCommentOperations and the request vote are all equal to 1.
    public function testVote5() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 
        $parameters2 = array(
            'file_ids' => '1;2', 
            'description' => 'this is the test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
            'duration' => '1440',
            'interaction_radius' => '100', 
        );  
        //create the medias. 
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication();    
        $parameters3 = array(
            'content' => 'This is the pin comment test', 
        );  
        //post comment pin_operations
        $response_comment = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters3, [], [], $this->transformHeadersToServerVars($server2));  
        $this->refreshApplication();    
        $content = array(
            'vote' => 'up', 
        );
        $response1 = $this->call('post', 'http://'.$this->domain.'/pins/comments/1/vote', $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();
        $response2 = $this->call('post', 'http://'.$this->domain.'/pins/comments/1/vote', $content, [], [], $this->transformHeadersToServerVars($server2));  
        $this->seeJson([
                 'message' => 'Bad request, you have already voted up!',
                 'error_code' => '400-12',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response2->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    } 

    // test the response when the pin_comment is not null and the vote in PinCommentOperations is 1 and the request vote is -1.
    public function testVote6() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 
        $parameters2 = array(
            'file_ids' => '1;2', 
            'description' => 'this is the test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
            'duration' => '1440',
            'interaction_radius' => '100', 
        );  
        //create the medias. 
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters2, [], [], $this->transformHeadersToServerVars($server2));  
        $this->refreshApplication();    
        $parameters3 = array(
            'content' => 'This is the pin comment test', 
        );  
        //post comment pin_operations
        $response_comment = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters3, [], [], $this->transformHeadersToServerVars($server2));  
        $this->refreshApplication();  
        $content = array(
            'vote' => 'up', 
        );
        $response1 = $this->call('post', 'http://'.$this->domain.'/pins/comments/1/vote', $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();
        $content2 = array(
            'vote' => 'down', 
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/pins/comments/1/vote', $content2, [], [], $this->transformHeadersToServerVars($server2));  
        $array2 = json_decode($response2->getContent());  
        $result = false;
        if ($response2->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
        $this->seeInDatabase('pin_comments', ['user_id' => 1, 'pin_id' => 1, 'type' => 'media', 'content' => 'This is the pin comment test', 'vote_up_count' => 0, 'vote_down_count' => 1]);
        $this->seeInDatabase('pin_comment_operations', ['user_id' => 1, 'pin_comment_id' => 1, 'vote' => -1]);
    } 

    // test the correct response of the candcelVote.
    public function testcancelVote() {  
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 
        $parameters2 = array(
            'file_ids' => '1;2', 
            'description' => 'this is the test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
            'duration' => '1440',
            'interaction_radius' => '100', 
        );  
        //create the medias. 
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters2, [], [], $this->transformHeadersToServerVars($server2));  
        $this->refreshApplication();    
        $parameters3 = array(
            'content' => 'This is the pin comment test', 
        );  
        //post comment pin_operations
        $response_comment = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters3, [], [], $this->transformHeadersToServerVars($server2));  
        $this->refreshApplication();  
        $content = array(
            'vote' => 'up', 
        );  
        $response1 = $this->call('post', 'http://'.$this->domain.'/pins/comments/1/vote', $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();
        $content2 = array(
            'vote' => 'down', 
        );
        $response2 = $this->call('delete', 'http://'.$this->domain.'/pins/comments/1/vote', $content2, [], [], $this->transformHeadersToServerVars($server2));  
        $array2 = json_decode($response2->getContent());  
        $this->seeInDatabase('pin_comments', ['user_id' => 1, 'pin_id' => 1, 'type' => 'media', 'vote_up_count' => 0, 'vote_down_count' => 0]); 
        $this->notseeInDatabase('pin_comment_operations', ['user_id' => 1, 'pin_comment_id' => 1]); 
        $result = false;
        if ($response2->status() == '204') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }  

    // test the response when the pin_comment_id is not integer.
    public function testcancelVote2() {  
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 
        $parameters2 = array(
            'file_ids' => '1;2', 
            'description' => 'this is the test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
            'duration' => '1440',
            'interaction_radius' => '100', 
        );  
        //create the medias. 
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters2, [], [], $this->transformHeadersToServerVars($server2));  
        $this->refreshApplication();    
        $parameters3 = array(
            'content' => 'This is the pin comment test', 
        );  
        //post comment pin_operations
        $response_comment = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters3, [], [], $this->transformHeadersToServerVars($server2));  
        $this->refreshApplication();  
        $content = array(
            'vote' => 'up', 
        );  
        $response1 = $this->call('post', 'http://'.$this->domain.'/pins/comments/1/vote', $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();
        $content2 = array(
            'vote' => 'down', 
        );
        $response2 = $this->call('delete', 'http://'.$this->domain.'/pins/comments/fae/vote', $content2, [], [], $this->transformHeadersToServerVars($server2));   
        $this->seeJson([
                 'message' => 'pin_comment_id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response2->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }  
    // test the response when the comment does not exist.
    public function testcancelVote3() {  
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 
        $parameters2 = array(
            'file_ids' => '1;2', 
            'description' => 'this is the test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
            'duration' => '1440',
            'interaction_radius' => '100', 
        );  
        //create the medias. 
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters2, [], [], $this->transformHeadersToServerVars($server2));   
        $this->refreshApplication();  
        $content = array(
            'vote' => 'up', 
        );  
        $response1 = $this->call('post', 'http://'.$this->domain.'/pins/comments/1/vote', $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();
        $content2 = array(
            'vote' => 'down', 
        );
        $response2 = $this->call('delete', 'http://'.$this->domain.'/pins/comments/1/vote', $content2, [], [], $this->transformHeadersToServerVars($server2));   
        $this->seeJson([
                 'message' => 'comment not found',
                 'error_code' => '404-9',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response2->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }  
    // test the response when there is no vote yet.
    public function testcancelVote4() {  
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 
        $parameters2 = array(
            'file_ids' => '1;2', 
            'description' => 'this is the test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
            'duration' => '1440',
            'interaction_radius' => '100', 
        );  
        //create the medias. 
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters2, [], [], $this->transformHeadersToServerVars($server2));  
        $this->refreshApplication();    
        $parameters3 = array(
            'content' => 'This is the pin comment test', 
        );  
        //post comment pin_operations
        $response_comment = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters3, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();
        $content2 = array(
            'vote' => 'down', 
        );
        $response2 = $this->call('delete', 'http://'.$this->domain.'/pins/comments/1/vote', $content2, [], [], $this->transformHeadersToServerVars($server2));   
        $this->seeJson([
                 'message' => 'Bad request, you have not voted yet!',
                 'error_code' => '400-14',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response2->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }  
    // the correct response of the feeling.
    public function testFeeling() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $parameters2 = array(
            'feeling' => 1, 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/feeling', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
        $this->seeInDatabase('pin_operations', ['user_id' => 1, 'pin_id' => 1, 'type' => 'media', 'feeling' => 1]); 
        $this->seeInDatabase('medias', ['user_id' => 1, 'description' => 'This is the test1', 'feeling_count' => '0,1,0,0,0,0,0,0,0,0,0']); 
    }

    // the the response when the type is media but the media information does not exist.
    public function testFeeling2() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $parameters2 = array(
            'feeling' => 1, 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/feeling', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'PIN not found',
                 'error_code' => '404-13',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    } 

    // // test the response when the format of the pin_id is wrong.
    public function testFeeling3() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $parameters2 = array(
            'feeling' => 1, 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/fae/feeling', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'pin_id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // // test the response when the type is wrong.
    public function testFeeling4() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/wrong/1/save', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'wrong type, neither media nor comment',
                 'error_code' => '400-7',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // // test the correct response of the method of unlike when the feeling is true.
    public function testRemoveFeeling() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $this->refreshApplication(); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440, 
        ]); 
        $parameters2 = array(
            'feeling' => 1, 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/feeling', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication(); 
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/media/1/feeling', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeInDatabase('pin_operations', ['user_id' => 1, 'pin_id' => 1, 'type' => 'media', 'feeling' => -1]); 
        $this->assertResponseStatus(204);
    }

    // // test the correct response of the method of unsave when the saved is false.
    public function testRemoveFeeling2() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $this->refreshApplication(); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440, 
        ]);  
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/media/1/feeling', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'Bad request, you have not post feeling of this pin yet!',
                 'error_code' => '400-15',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // // test the response when the pin_operations information does not exist in the database.
    public function testRemoveFeeling3() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/media/1/feeling', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'PIN not found',
                 'error_code' => '404-13',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // // test the response when the format of the pin_id is wrong.
    public function testRemoveFeeling4() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440, 
        ]); 
        $parameters2 = array(
            'feeling' => 1, 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/feeling', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication(); 
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/media/fae/save', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'pin_id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // test the response when the type is wrong.
    public function testRemoveFeeling5() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440, 
        ]); 
        $parameters2 = array(
            'feeling' => 1, 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/feeling', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication(); 
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/wrong/1/save', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'wrong type, neither media nor comment',
                 'error_code' => '400-7',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // test the correct response of the update comment.
    public function testUpdateComment() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $array = json_decode($login_response->getContent());
        $session = Sessions::find(1); 
        //big difference of the geolocation radius;
        $session->location = new Point(34.2799, -118.2799);
        $session->save();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440,
            'interaction_radius' => 1
        ]); 
        $parameters = array(
            'content' => 'this is the comment pin test.', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();  
        $parameters2 = array(
            'content' => 'this is the new comment pin test.', 
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/pins/comments/1', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $result = false;
        if ($response2->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
        $this->seeInDatabase('pin_comments', ['user_id' => 1, 'pin_id' => 1, 'type' => 'media', 'content' => 'this is the new comment pin test.']);
    }

    // test whether the input format is right or wrong.
    public function testUpdateComment2() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $array = json_decode($login_response->getContent());
        $session = Sessions::find(1); 
        //big difference of the geolocation radius;
        $session->location = new Point(34.2799, -118.2799);
        $session->save();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440,
            'interaction_radius' => 1
        ]); 
        $parameters = array(
            'content' => 'this is the comment pin test.', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();  
        $parameters2 = array( 

        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/pins/comments/1', $parameters2, [], [], $this->transformHeadersToServerVars($server2));  
        $array2 = json_decode($response2->getContent());    
        $result = false;
        if ($response2->status() == '422' && $array2->message == 'Could not update comment.' && $array2->errors->content[0] == 'The content field is required when anonymous is not present.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // the the response when the pin_comment does not exist.
    public function testUpdateComment3() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $array = json_decode($login_response->getContent());
        $session = Sessions::find(1); 
        //big difference of the geolocation radius;
        $session->location = new Point(34.2799, -118.2799);
        $session->save();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440,
            'interaction_radius' => 1
        ]);    
        $parameters2 = array( 
            'content' => 'this is the new comment pin test'
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/pins/comments/1', $parameters2, [], [], $this->transformHeadersToServerVars($server2));  
        $this->seeJson([
                 'message' => 'comment not found',
                 'error_code' => '404-9',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response2->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    } 
    //test the response when the format of pin_comment_id is wrong.
    public function testUpdateComment4() {  
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $array = json_decode($login_response->getContent());
        $session = Sessions::find(1); 
        //big difference of the geolocation radius;
        $session->location = new Point(34.2799, -118.2799);
        $session->save();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440,
            'interaction_radius' => 1
        ]);    
        $parameters = array(
            'content' => 'this is the comment pin test.', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();  
        $parameters2 = array( 
            'content' => 'this is the new comment pin test'
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/pins/comments/fae', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'pin_comment_id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response2->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    //test the response when the user_id is not the self_user_id.
    public function testUpdateComment5() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameter2 = array(
            'email' => 'letsfae2@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp2',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter2, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $array = json_decode($login_response->getContent());
        $session = Sessions::find(1); 
        //big difference of the geolocation radius;
        $session->location = new Point(34.2799, -118.2799);
        $session->save();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),  
            'file_ids' => '1;2',
            'duration' => 1440,
            'interaction_radius' => 1
        ]);    
        $parameters = array(
            'content' => 'this is the comment pin test.', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();  
        $parameters = array(
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response2 = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array2 = json_decode($login_response2->getContent()); 
        $server3 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array2->debug_base64ed,
        ); 
        $this->refreshApplication(); 
        $parameters2 = array( 
            'content' => 'this is the new comment pin test'
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/pins/comments/1', $parameters2, [], [], $this->transformHeadersToServerVars($server3)); 
        $this->seeJson([
                 'message' => 'You can not update this comment',
                 'error_code' => '403-2',
                 'status_code' => '403', 
        ]); 
        $result = false;
        if ($response2->status() == '403') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // test the correct response of the pinStatistics.
    public function testPinStatistics() { 
        $this->markTestSkipped(); 
        //register of the user.
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $array = json_decode($login_response->getContent());
        $session = Sessions::find(1); 
        //big difference of the geolocation radius;
        $session->location = new Point(34.2799, -118.2799);
        $session->save();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save(); 

        $parameters = array(
            'file_ids' => '1;2', 
            'description' => 'this is a test',
            'geo_latitude' => 34.2799,
            'geo_longitude' => -118.2799,
            'duration' => 1440,
            'interaction_radius' => 100,
            'anonymous' => 'true'
        );
        $media_response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));    
        $this->refreshApplication(); 
        $saved_media_response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/save', [], [], [], $this->transformHeadersToServerVars($server2));  
        $this->refreshApplication(); 
        $parameters = array(
            'content' => 'this is the comment pin test.', 
        );
        $pin_comment_response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication(); 
        $parameters2 = array(
            'content' => 'This is the test.',
            'geo_longitude' => '-118.2799',
            'geo_latitude' => '34.2799', 
            'duration' => '1440',
            'interaction_radius' => '100',
            'anonymous' => 'true',
        ); 
        //create the comment.
        $comment_response = $this->call('post', 'http://'.$this->domain.'/comments', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();
        $response = $this->call('get', 'http://'.$this->domain.'/pins/statistics', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent()); 
        $result = false; 
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
        $this->seeJson([
            'user_id' => '1' ,
            'count' => array(
                'created_comment_pin' => 1,
                'created_media_pin' => 1,
                'created_location' => 0,
                'created_chat_room' => 0,
                'saved_comment_pin' => 0,
                'saved_media_pin' => 1,
                'saved_place_pin' => 0
            )
        ]); 
    }
}
