<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Files; 
use App\Medias; 
use App\Tags; 
use App\Pin_operations;

class MediaTest extends TestCase {
    /**
     * A basic test example.
     *
     * @return void
     */
    // use DatabaseMigrations;
    /** @test */
    public function setUp() {
        parent::setUp();
        $this->domain = Config::get('api.domain');  
        $this->markTestSkipped(); 
    } 

    public function tearDown() {
        $this->beforeApplicationDestroyed(function () {
            DB::disconnect();
        });
        parent::tearDown();
    }
    //test correct response of the method of creatMedia.
    public function testCreate() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
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

        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'file_ids' => '1;2',
            'tag_ids' => '1;2',
            'description' => 'this is a test',
            'geo_latitude' => 34.2799,
            'geo_longitude' => -118.2799,
            'duration' => 1440,
            'interaction_radius' => 100,
            'anonymous' => 'true'
        );
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->seeJson([
                'media_id' => 1,
        ]);
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
        $this->seeInDatabase('medias', ['user_id' => 1, 'description' => 'this is a test', 'tag_ids' => '1;2', 'file_ids' => '1;2', 'geolocation' => '0101000020E6100000A089B0E1E9915DC0401361C3D3234140', 'duration' => 1440, 'interaction_radius' => 100,  'anonymous' => true]);
        $this->seeInDatabase('tags', ['user_id' => 1, 'title' => 'fae', 'color' => '#fff000', 'reference_count' => 1]);
        $this->seeInDatabase('tags', ['user_id' => 1, 'title' => 'fae1', 'color' => '#fff000', 'reference_count' => 1]);
        $this->seeInDatabase('files', ['user_id' => 1, 'type' => 'video', 'mine_type' => 'video', 'size' => 256, 'reference_count' => 1]);
        $this->seeInDatabase('files', ['user_id' => 1, 'type' => 'image', 'mine_type' => 'image', 'size' => 256, 'reference_count' => 1]);
        $this->seeInDatabase('pin_helper', ['type' => 'media', 'pin_id' => 1, 'geolocation' => '0101000020E6100000A089B0E1E9915DC0401361C3D3234140', 'duration' => 1440, 'anonymous' => true, 'user_id' => 1]);
    }

    //test the response when the tag information does not exist with the tag_ids.
    public function testCreate2() {
        $this->markTestSkipped();
        $parameter1 = array(
            'email' => 'letsfae@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'user_name' => 'faeapp',
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
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

        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'file_ids' => '1;2',
            'tag_ids' => '1;2',
            'description' => 'this is a test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
            'duration' => '1440',
            'interaction_radius' => '100', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        var_dump($response);
        $array2 = json_decode($response->getContent());  
        $result = false;
        if ($response->status() == '404' && $array2->message == 'tags not exist') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test whether the input format is right.
    public function testCreate3() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
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

        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        //the format of the geo_longitude is wrong.
        $parameters = array(
            'file_ids' => '1;2',
            'tag_ids' => '1;2',
            'description' => 'this is a test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-218.2799',
            'duration' => 1440,
            'interaction_radius' => 100
        );
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent());   
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Could not create media.' && $array2->errors->geo_longitude[0] == 'The geo longitude must be between -180 and 180.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the response when the file information does not exist with the file_ids.
    public function testCreate4() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
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

        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'file_ids' => '1;2',
            'tag_ids' => '1;2',
            'description' => 'this is a test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
            'duration' => 1440,
            'interaction_radius' => 100
        );
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent());  
        $result = false;
        if ($response->status() == '404' && $array2->message == 'files not exist') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test the response when the request has no tag_ids.
    public function testCreate5() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
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

        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'file_ids' => '1;2', 
            'description' => 'this is a test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
            'duration' => 1440, 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->seeJson([
                'media_id' => 1,
        ]);
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
        $this->seeInDatabase('medias', ['user_id' => 1, 'description' => 'this is a test', 'file_ids' => '1;2']); 
        $this->seeInDatabase('files', ['user_id' => 1, 'type' => 'video', 'mine_type' => 'video', 'size' => 256, 'reference_count' => 1]);
        $this->seeInDatabase('files', ['user_id' => 1, 'type' => 'image', 'mine_type' => 'image', 'size' => 256, 'reference_count' => 1]);
    }

    //test correct response of the method of updateMedia.
    public function testUpdate() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
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

        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'file_ids' => '1;2',
            'tag_ids' => '1;2',
            'description' => 'this is a test',
            'geo_latitude' => 34.2799,
            'geo_longitude' => -118.2799,
            'duration' => 1440, 
            'interaction_radius' => '100', 
            'anonymous' => 'true'
        );
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->refreshApplication();
        $parameters1 = array( 
            'description' => 'this is a test2', 
            'geo_latitude' => 35.5799,
            'geo_longitude' => -120.2799,
            'duration' => 1440,
            'interaction_radius' => '100',
            'anonymous' => 'true'
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/medias/1', $parameters1, [], [], $this->transformHeadersToServerVars($server2));  
        $array3 = json_decode($response2->getContent()); 
        $result = false;
        if ($response2->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
        $this->seeInDatabase('medias', ['user_id' => 1, 'description' => 'this is a test2', 'tag_ids' => '1;2', 'file_ids' => '1;2', 'geolocation' => '0101000020E6100000A089B0E1E9115EC0A779C7293ACA4140', 'duration' => 1440, 'interaction_radius' => 100,'anonymous' => 'true']);
        $this->seeInDatabase('pin_helper', ['geolocation' => '0101000020E6100000A089B0E1E9115EC0A779C7293ACA4140', 'duration' => 1440, 'anonymous' => 'true']);
    }

    //test whether the input format of the media_id is right.
    public function testUpdate2() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
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

        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'file_ids' => '1;2',
            'tag_ids' => '1;2',
            'description' => 'this is a test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->refreshApplication();
        $parameters1 = array( 
            'description' => 'this is a test2', 
        );
        //wrong format of the media_id.
        $response2 = $this->call('post', 'http://'.$this->domain.'/medias/fae', $parameters1, [], [], $this->transformHeadersToServerVars($server2));  
        $array3 = json_decode($response2->getContent()); 
        $result = false;
        if ($response2->status() == '400' && $array3->message == 'Bad Request') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test whether the input format is right.
    public function testUpdate3() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
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

        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'file_ids' => '1;2',
            'tag_ids' => '1;2',
            'description' => 'this is a test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->refreshApplication();
        //the format of the geo_longitude is wrong.
        $parameters1 = array( 
            'description' => 'this is a test2', 
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-218.2799',
        );
        //wrong format of the media_id.
        $response2 = $this->call('post', 'http://'.$this->domain.'/medias/1', $parameters1, [], [], $this->transformHeadersToServerVars($server2));  
        $array3 = json_decode($response2->getContent());  
        $result = false;
        if ($response2->status() == '422' && $array3->message == 'Could not update media.' && $array3->errors->geo_longitude[0] == 'The geo longitude must be between -180 and 180.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the response when the media information does not exist with the media_id.
    public function testUpdate4() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
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

        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'file_ids' => '1;2',
            'tag_ids' => '1;2',
            'description' => 'this is a test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->refreshApplication();
        $parameters1 = array( 
            'description' => 'this is a test2', 
        );
        //The media_id does not exist.
        $response2 = $this->call('post', 'http://'.$this->domain.'/medias/-1', $parameters1, [], [], $this->transformHeadersToServerVars($server2));  
        $array3 = json_decode($response2->getContent()); 
        $result = false;
        if ($response2->status() == '404' && $array3->message == 'Not Found') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test the response when the tag information does not exist with the tag_ids.
    public function testUpdate5() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
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

        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'file_ids' => '1;2',
            'tag_ids' => '1;2',
            'description' => 'this is a test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->refreshApplication();
        $parameters1 = array( 
            'description' => 'this is a test2', 
            'tag_ids' => '3',
        ); 
        $response2 = $this->call('post', 'http://'.$this->domain.'/medias/1', $parameters1, [], [], $this->transformHeadersToServerVars($server2));  
        $array3 = json_decode($response2->getContent()); 
        $result = false;
        if ($response2->status() == '404' && $array3->message == 'Not Found') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test the response when the request of tag_ids is null.
    public function testUpdate6() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
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

        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'file_ids' => '1;2',
            'tag_ids' => '1;2',
            'description' => 'this is a test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->refreshApplication();
        $parameters1 = array( 
            'description' => 'this is a test2', 
             'tag_ids' => 'null',
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/medias/1', $parameters1, [], [], $this->transformHeadersToServerVars($server2));  
        $array3 = json_decode($response2->getContent()); 
        $result = false;
        if ($response2->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
        $this->seeInDatabase('medias', ['user_id' => 1, 'description' => 'this is a test2', 'tag_ids' => null, 'file_ids' => '1;2']);
    }

    //test the response when the request of tag_ids exists in the tag table. 
    public function testUpdate7() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
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
        $tags = new Tags;
        $tags->title = 'fae2';
        $tags->color = '#fff000';
        $tags->user_id = 1;  
        $tags->reference_count = 0;
        $tags->save();

        $tags = new Tags;
        $tags->title = 'fae3';
        $tags->color = '#fff000';
        $tags->user_id = 1;  
        $tags->reference_count = 0;
        $tags->save();

        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'file_ids' => '1;2',
            'tag_ids' => '1;2',
            'description' => 'this is a test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->refreshApplication();
        $parameters1 = array( 
            'description' => 'this is a test2', 
             'tag_ids' => '3;4',
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/medias/1', $parameters1, [], [], $this->transformHeadersToServerVars($server2));  
        $array3 = json_decode($response2->getContent()); 
        $result = false;
        if ($response2->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
        $this->seeInDatabase('medias', ['user_id' => 1, 'description' => 'this is a test2', 'tag_ids' => '3;4', 'file_ids' => '1;2']);
        $this->seeInDatabase('tags', ['user_id' => 1, 'title' => 'fae2', 'color' => '#fff000', 'reference_count' => 1]);
        $this->seeInDatabase('tags', ['user_id' => 1, 'title' => 'fae3', 'color' => '#fff000', 'reference_count' => 1]);
    }

    //test correct response of the method of getOneMedia.
    public function testGetOne() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
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

        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'file_ids' => '1;2',
            'tag_ids' => '1;2',
            'description' => 'this is a test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
             'duration' => 1440, 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->refreshApplication(); 
        $response_like = $this->call('post', 'http://'.$this->domain.'/pins/media/1/like', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication();
        //post save pin_operations
        $response_save = $this->call('post', 'http://'.$this->domain.'/pins/media/1/save', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication();
        $parameters1 = array(
            'content' => 'This is the pin comment test', 
        );  
        //post comment pin_operations
        $response_comment = $this->call('post', 'http://'.$this->domain.'/pins/media/1/comments', $parameters1, [], [], $this->transformHeadersToServerVars($server2));  
        $this->refreshApplication();  
        $response2 = $this->call('get', 'http://'.$this->domain.'/medias/1', [], [], [], $this->transformHeadersToServerVars($server2));
        $array3 = json_decode($response2->getContent());  
        $this->seeJson([ 
                    'media_id' => 1,
                    'user_id' => 1,
                    'file_ids' => array('1','2'),
                    'tag_ids' => array('1','2'),
                    'description' => 'this is a test',
                    'anonymous' => false,
                    'geolocation' => array(
                        'latitude' => -89.99,
                        'longitude' => -118.2799,
                    ),   
                    'liked_count' => 1,
                    'saved_count' => 1,
                    'comment_count' => 1,
                    'created_at' => $array3->created_at,
                    'user_pin_operations' => array(
                        'is_liked' => true,
                        'liked_timestamp' => $array3->user_pin_operations->liked_timestamp,
                        'is_saved' => true,
                        'saved_timestamp' => $array3->user_pin_operations->saved_timestamp,
                        'is_read' => true,
                        'read_timestamp' => $array3->user_pin_operations->read_timestamp,
                    ), 
        ]);
        $result = false;
        if ($response2->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test whether the input format of the media_id is right.
    public function testGetOne2() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
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

        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'file_ids' => '1;2',
            'tag_ids' => '1;2',
            'description' => 'this is a test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->refreshApplication(); 
        //wrong format of the media_id.
        $response2 = $this->call('get', 'http://'.$this->domain.'/medias/fae', [], [], [], $this->transformHeadersToServerVars($server2));  
        $array3 = json_decode($response2->getContent()); 
        $result = false;
        if ($response2->status() == '400' && $array3->message == 'Bad Request') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the response when the media information does not exist with the media_id.
    public function testGetOne3() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
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

        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'file_ids' => '1;2',
            'tag_ids' => '1;2',
            'description' => 'this is a test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->refreshApplication(); 
        //The media_id does not exist.
        $response2 = $this->call('get', 'http://'.$this->domain.'/medias/-1', [], [], [], $this->transformHeadersToServerVars($server2));  
        $array3 = json_decode($response2->getContent()); 
        $result = false;
        if ($response2->status() == '404' && $array3->message == 'Not Found') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test correct response of the method of deleteMedia.
    public function testDelete() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
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

        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'file_ids' => '1;2',
            'tag_ids' => '1;2',
            'description' => 'this is a test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->refreshApplication(); 
        $response2 = $this->call('delete', 'http://'.$this->domain.'/medias/1', [], [], [], $this->transformHeadersToServerVars($server2));  
        $array3 = json_decode($response2->getContent());  
        $this->assertResponseStatus(204);
    }

    //test whether the input format of the media_id is right.
    public function testDelete2() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
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

        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'file_ids' => '1;2',
            'tag_ids' => '1;2',
            'description' => 'this is a test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->refreshApplication(); 
        //wrong format of the media_id.
        $response2 = $this->call('delete', 'http://'.$this->domain.'/medias/fae', [], [], [], $this->transformHeadersToServerVars($server2));  
        $array3 = json_decode($response2->getContent()); 
        $result = false;
        if ($response2->status() == '400' && $array3->message == 'Bad Request') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the response when the media information does not exist with the media_id.
    public function testDelete3() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
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

        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'file_ids' => '1;2',
            'tag_ids' => '1;2',
            'description' => 'this is a test',
            'geo_latitude' => '-89.99',
            'geo_longitude' => '-118.2799',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/medias', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->refreshApplication(); 
        //The media_id does not exist.
        $response2 = $this->call('delete', 'http://'.$this->domain.'/medias/-1', [], [], [], $this->transformHeadersToServerVars($server2));  
        $array3 = json_decode($response2->getContent()); 
        $result = false;
        if ($response2->status() == '404' && $array3->message == 'Not Found') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test the response when the deleted user_id is not the same as the self_user_id.
    public function testDelete4() {
        $this->markTestSkipped();
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
            'email' => 'letsfae@yahoo.com',
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 
        $this->refreshApplication();   
        $tags = new Tags;
        $tags->title = 'fae1';
        $tags->color = '#fff000';
        $tags->user_id = 1;  
        $tags->reference_count = 1;
        $tags->save();

        $tags = new Tags;
        $tags->title = 'fae2';
        $tags->color = '#fff000';
        $tags->user_id = 1;  
        $tags->reference_count = 1;
        $tags->save();

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
        $tags->title = 'fae3';
        $tags->color = '#fff000';
        $tags->user_id = 2;  
        $tags->reference_count = 1;
        $tags->save();

        $tags = new Tags;
        $tags->title = 'fae4';
        $tags->color = '#fff000';
        $tags->user_id = 2;  
        $tags->reference_count = 1;
        $tags->save();

        $files = new Files;
        $files->user_id = 2;
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
        $files->user_id = 2;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();

        $medias = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test2.',
            'geolocation' => '0101000020E6100000A089B0E1E9915DC08FC2F5285C7F56C0', 
            'tag_ids' => '1;2',
            'file_ids' => '1;2'
        ]); 

        $medias = Medias::create([
            'user_id' => 2,
            'description' => 'This is the test2.',
            'geolocation' => '0101000020E6100000A089B0E1E9915DC08FC2F5285C7F56C0', 
            'tag_ids' => '3;4',
            'file_ids' => '3;4' 
        ]);
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $response2 = $this->call('delete', 'http://'.$this->domain.'/medias/2', [], [], [], $this->transformHeadersToServerVars($server2));  
        $array3 = json_decode($response2->getContent()); 
        $result = false;
        if ($response2->status() == '403' && $array3->message == 'You can not delete this media') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test correct response of the method of getFromUser and the request user_id is not the same as the logged in user_id.
    public function testGetFromUser() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 

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
            // sleep(1);
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
         //get the medias of the user with the user_id.
        //get the medias of the page 1.
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/medias/users/1', $content, [], [], $this->transformHeadersToServerVars($server3));
        $array2 = json_decode($response_page1->getContent());   
        for ($i = 0; $i < 30; $i++) {
            $this->seeJson([ 
                    'media_id' => 30 - $i,
                    'user_id' => 1,
                    'file_ids' => array('1','2'),
                    'tag_ids' => array('1','2'),
                    'description' => 'this is the test'.(30 - (++$i)), 
                    'geolocation' => array(
                        'latitude' => -89.99,
                        'longitude' => -118.2799,
                    ), 
                    'liked_count' => 1, 
                    'saved_count' => 1, 
                    'comment_count' => 1,
                    'created_at' => $array2[$i]->created_at,
                    'user_pin_operations' => array(
                        'is_liked' => false,
                        'liked_timestamp' => $array2[$i]->user_pin_operations->liked_timestamp,
                        'is_saved' => false,
                        'saved_timestamp' => $array2[$i]->user_pin_operations->saved_timestamp,
                        'is_read' => false,
                        'read_timestamp' => $array2[$i]->user_pin_operations->read_timestamp,
                    ),
             ]); 
        }
        $this->refreshApplication();
        $content2 = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 2,
        );
        // //get the medias of the page 2.
        $response_page2 = $this->call('get', 'http://'.$this->domain.'/medias/users/1', $content2, [], [], $this->transformHeadersToServerVars($server3)); 
        $array3 = json_decode($response_page2->getContent()); 
        $this->seeJson([ 
                    'media_id' => 1,
                    'user_id' => 1,
                    'file_ids' => array('1','2'),
                    'tag_ids' => array('1','2'),
                    'description' => 'this is the test0', 
                    'geolocation' => array(
                        'latitude' => -89.99,
                        'longitude' => -118.2799,
                    ), 
                    'liked_count' => 1, 
                    'saved_count' => 1, 
                    'comment_count' => 1,
                    'created_at' => $array3[0]->created_at,
                    'user_pin_operations' => array(
                        'is_liked' => false,
                        'liked_timestamp' => $array3[0]->user_pin_operations->liked_timestamp,
                        'is_saved' => false,
                        'saved_timestamp' => $array3[0]->user_pin_operations->saved_timestamp,
                        'is_read' => false,
                        'read_timestamp' => $array3[0]->user_pin_operations->read_timestamp,
                    ),
             ]);
        $result = false;
        if ($response_page1->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test whether the input format of the user_id is right.
    public function testGetFromUser2() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 

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
                ); 
        }
        //create the medias.
        for ($i = 0; $i < 31; $i++) {
            $response[$i] = $this->call('post', 'http://'.$this->domain.'/medias', $parameters[$i], [], [], $this->transformHeadersToServerVars($server2));   
            $this->refreshApplication();
        } 
        $content = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        );
        //wrong format of the user_id.
        //get the faevors of the page 1.
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/medias/users/fae', $content, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response_page1->getContent()); 
        $result = false;
        if ($response_page1->status() == '400' && $array2->message == 'id should be integer') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test the response when the user information does not exist with the user_id. 
    public function testGetFromUser3() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 

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
                ); 
        }
        //create the medias.
        for ($i = 0; $i < 31; $i++) {
            $response[$i] = $this->call('post', 'http://'.$this->domain.'/medias', $parameters[$i], [], [], $this->transformHeadersToServerVars($server2));   
            $this->refreshApplication();
        } 
        $content = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        );
        //The user_id does not exist.
        //get the faevors of the page 1.
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/medias/users/-1', $content, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response_page1->getContent()); 
        $result = false;
        if ($response_page1->status() == '404' && $array2->message == 'user does not exist') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test whenther the input format is right.
    public function testGetFromUser4() {
        $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent()); 

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
                ); 
        }
        //create the medias.
        for ($i = 0; $i < 31; $i++) {
            $response[$i] = $this->call('post', 'http://'.$this->domain.'/medias', $parameters[$i], [], [], $this->transformHeadersToServerVars($server2));   
            $this->refreshApplication();
        } 
        //wrong format of the start_time.
        $content = array(
            'start_time' => '2016-06-08',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        ); 
        //get the faevors of the page 1.
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/medias/users/1', $content, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response_page1->getContent());   
        $result = false;
        if ($response_page1->status() == '422' && $array2->message == 'Could not get user medias.' && $array2->errors->start_time[0] == 'The start time does not match the format Y-m-d H:i:s.') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }
}
