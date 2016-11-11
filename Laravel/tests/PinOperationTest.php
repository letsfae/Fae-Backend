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
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2));
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
        $this->seeInDatabase('pin_operations', ['user_id' => 1, 'pin_id' => 1, 'type' => 'media', 'liked' => true]); 
    }

    // the the response when the type is media but the media information does not exist.
    public function testLike2() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
        $array2 = json_decode($response->getContent()); 
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Bad request, No such pin exist!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the response when the Pin_operations information exists in the database.
    public function testLike3() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
            'file_ids' => '1;2'
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2));
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
        $this->seeInDatabase('pin_operations', ['user_id' => 1, 'pin_id' => 1, 'type' => 'media', 'liked' => true]); 
    }
    // test the correct response of the method of unlike when the saved is false.
    public function testUnLike() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->assertResponseStatus(204); 
    }

    // test the correct response of the method of unlike when the saved is true.
    public function testUnLike2() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
            'saved' => true,   
        ]);
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->assertResponseStatus(204); 
        $this->seeInDatabase('pin_operations', ['user_id' => 1, 'pin_id' => 1, 'type' => 'media', 'liked' => false]); 
    }
    // test the response when the pin_operations information does not exist in the database.
    public function testUnLike3() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
        $array2 = json_decode($response->getContent()); 
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Bad request, never liked such pin!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // the correct response of the save.
    public function testSave() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
        $array2 = json_decode($response->getContent()); 
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Bad request, No such pin exist!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the response when the Pin_operations information exists in the database.
    public function testSave3() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
            'file_ids' => '1;2'
        ]); 
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/save', [], [], [], $this->transformHeadersToServerVars($server2));
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
        $this->seeInDatabase('pin_operations', ['user_id' => 1, 'pin_id' => 1, 'type' => 'media', 'saved' => true]); 
    }

    // test the correct response of the method of unlike when the saved is false.
    public function testUnSave() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/media/1/save', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->assertResponseStatus(204);
    }

    // test the correct response of the method of unsave when the liked is true.
    public function testUnSave2() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
            'saved' => true,   
        ]);
        $response = $this->call('delete', 'http://'.$this->domain.'/pins/media/1/save', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->assertResponseStatus(204); 
        $this->seeInDatabase('pin_operations', ['user_id' => 1, 'pin_id' => 1, 'type' => 'media', 'saved' => false]); 
    }

    // test the response when the pin_operations information does not exist in the database.
    public function testUnSave3() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
        $array2 = json_decode($response->getContent()); 
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Bad request, never saved such pin!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the correct response of the comment.
    public function testComment() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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

    // test whether the input format is right of wrong.
    public function testComment2() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
        $array2 = json_decode($response->getContent());  
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Bad request, No such pin exist!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the correct response of the unComment.
    public function testUnComment() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
            'content' => 'this is the comment pin test.', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters, [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication();
        $response2 = $this->call('delete', 'http://'.$this->domain.'/pins/comments/1', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->assertResponseStatus(204);
    }

    // test the response when the pin_comments information does not exist in the database.
    public function testUnComment2() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
        $array2 = json_decode($response->getContent());  
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Bad request, never commented such pin!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the correct response of the getPinAttribute.
    public function testGetPinAttribute() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
            'file_ids' => '1;2'
        ]); 
        $pin_operations1 = Pin_operations::create([
            'type' => 'media',
            'pin_id' => 1,
            'user_id' => 1,
            'liked' =>  true,
            'saved' => true,   
        ]);
        $pin_operations2 = Pin_operations::create([
            'type' => 'media',
            'pin_id' => 2,
            'user_id' => 1,
            'liked' =>  true,
            'saved' => true,   
        ]);
        $pin_comments = pin_comments::create([
            'type' => 'media',
            'pin_id' => 1,
            'user_id' => 1,
            'content' => 'this is the comment test.'
        ]);
        $response = $this->call('get', 'http://'.$this->domain.'/pins/media/1/attribute', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                'type' => 'media',
                'pin_id' => 1,
                'likes' => 1,
                'saves' => 1,
                'comments' => 1 
        ]);
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
    }

    // test the response when the type is media but the media information does not exist.
    public function testGetPinAttribute2() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
        $pin_operations1 = Pin_operations::create([
            'type' => 'media',
            'pin_id' => 1,
            'user_id' => 1,
            'liked' =>  true,
            'saved' => true,   
        ]);
        $pin_operations2 = Pin_operations::create([
            'type' => 'media',
            'pin_id' => 2,
            'user_id' => 1,
            'liked' =>  true,
            'saved' => true,   
        ]);
        $pin_comments = pin_comments::create([
            'type' => 'media',
            'pin_id' => 1,
            'user_id' => 1,
            'content' => 'this is the comment test.'
        ]);
        $response = $this->call('get', 'http://'.$this->domain.'/pins/media/1/attribute', [], [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent());  
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Bad request, No such pin exist!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the correct response of the getPinCommentList.
    public function testGetPinCommentList() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
            'file_ids' => '1;2'
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
        // get the pinComments of the page 1.
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/pins/media/1/comments', $content, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response_page1->getContent());  
        for ($i = 0; $i < 30; $i++) {
            $this->seeJson([  
                        'pin_comment_id' => $array2[$i]->pin_comment_id,
                        'user_id' => $array2[$i]->user_id,
                        'content' => $array2[$i]->content, 
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
                    'pin_comment_id' => $array3[0]->pin_comment_id,
                    'user_id' => $array3[0]->user_id,
                    'content' => $array3[0]->content, 
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
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
        $array2 = json_decode($response_page1->getContent());   
        $result = false;
        if ($response_page1->status() == '422' && $array2->message == 'Bad request, No such pin exist!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the select page is larger than the total page.
    public function testGetPinCommentList3() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
            'file_ids' => '1;2'
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
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
            'file_ids' => '1;2'
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
        $response = $this->call('get', 'http://'.$this->domain.'/pins/media/1/comments', $content, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent());  
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Could not get pin comments.' && $array2->errors->start_time[0] == 'The start time does not match the format Y-m-d H:i:s.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the correct response of the getUserPinList.
    public function testGetUserPinList() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
            'file_ids' => '1;2'
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
        // get the pinComments of the page 1.
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/pins/users/1', $content, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response_page1->getContent());  
        for ($i = 0; $i < 30; $i++) {
            $this->seeJson([  
                        'pin_id' => $array2[$i]->pin_id, 
                        'type' => $array2[$i]->type, 
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
        $response_page2 = $this->call('get', 'http://'.$this->domain.'/pins/users/1', $content2, [], [], $this->transformHeadersToServerVars($server2)); 
        $array3 = json_decode($response_page2->getContent()); 
        $this->seeJson([ 
                    'pin_id' => $array3[0]->pin_id,
                    'type' => $array3[0]->type, 
                    'created_at' => $array3[0]->created_at, 
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
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
            'file_ids' => '1;2'
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
        $response = $this->call('get', 'http://'.$this->domain.'/pins/users/2', $content, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent());   
        $result = false; 
        if ($response->status() == '404' && $array2->message == 'Not Found') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
    }

    //test whether the format of the user_id is right. 
    public function testGetUserPinList3() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
            'file_ids' => '1;2'
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
        $array2 = json_decode($response->getContent());   
        $result = false;
        if ($response->status() == '400' && $array2->message == 'Bad Request') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    // test the select page is larger than the total page.
    public function testGetUserPinList4() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
            'file_ids' => '1;2'
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
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
            'file_ids' => '1;2'
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
        if ($response->status() == '422' && $array2->message == 'Could not get pin comments.' && $array2->errors->start_time[0] == 'The start time does not match the format Y-m-d H:i:s.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // test the correct response of the getSelfPinList.
    public function testGetSelfPinList() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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
            'file_ids' => '1;2'
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
        // get the pinComments of the page 1.
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/pins/users', $content, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response_page1->getContent());  
        for ($i = 0; $i < 30; $i++) {
            $this->seeJson([  
                        'pin_id' => $array2[$i]->pin_id, 
                        'type' => $array2[$i]->type, 
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
        $response_page2 = $this->call('get', 'http://'.$this->domain.'/pins/users', $content2, [], [], $this->transformHeadersToServerVars($server2)); 
        $array3 = json_decode($response_page2->getContent()); 
        $this->seeJson([ 
                    'pin_id' => $array3[0]->pin_id,
                    'type' => $array3[0]->type, 
                    'created_at' => $array3[0]->created_at, 
        ]);
        $result = false;
        if ($response_page1->headers->get('page') == '1' && $response_page1->headers->get('total-pages') == '2' && $response_page1->status() == '200') {
            $result = true;
        } 
        $this->assertEquals(true, $result);
    }
 
    // test the correct response of the getSavedPinList.
    public function testGetSavedPinList() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
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

        for ($i = 1; $i < 32; $i++) {
            $media = Medias::create([
                'user_id' => 1,
                'description' => 'This is the test1',
                'geolocation' => new Point(34.031958,-118.288125),  
                'file_ids' => '1;2'
            ]);  
        } 
        for ($i = 1; $i < 32; $i++) {
            $response = $this->call('post', 'http://'.$this->domain.'/pins/media/'.$i.'/save', [], [], [], $this->transformHeadersToServerVars($server2));
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
                        'type' => $array2[$i]->type, 
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
        $response_page2 = $this->call('get', 'http://'.$this->domain.'/pins/saved', $content2, [], [], $this->transformHeadersToServerVars($server2)); 
        $array3 = json_decode($response_page2->getContent()); 
        $this->seeJson([ 
                    'pin_id' => $array3[0]->pin_id,
                    'type' => $array3[0]->type, 
                    'created_at' => $array3[0]->created_at, 
        ]);
        $result = false;
        if ($response_page1->headers->get('page') == '1' && $response_page1->headers->get('total-pages') == '2' && $response_page1->status() == '200') {
            $result = true;
        } 
        $this->assertEquals(true, $result);
    }
}
