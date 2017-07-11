<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Tags;
use App\Files;

class TagsTest extends TestCase {
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
    //test correct response of the method of creatTags. 
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
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'title' => 'fae',
            'color' => '#1f1f1F',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/tags', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent());  
        $this->seeJson([
                'tag_id' => 1,
        ]);
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
        $this->seeInDatabase('tags', ['title' => 'fae', 'color' => '#1f1f1F', 'user_id' => 1]);
    }

    //test whether the input format is right. 
    public function testCreate2() {
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
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        //the color format is wrong.
        $parameters = array(
            'title' => 'fae',
            'color' => '#1f1f1Z',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/tags', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent());  
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Could not create new tag.' && $array2->errors->color[0] == 'The color format is invalid.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the response when the tag information has existed in the database. 
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
        $tag = Tags::create([
            'title' => 'fae',
            'color' => '1f1f1F',
            'user_id' => 1, 
        ]);
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );    
        $parameters = array(
            'title' => 'fae',
            'color' => '#1f1f1F',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/tags', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->seeJson([
                'tag_id' => 1,
        ]);
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test correct response of the method of getArray.
    public function testGetArray() {
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
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array();
        $response = array();
        $tag = array();
        for ($i = 0; $i < 31; $i++) {
            $parameters[$i] = array(
                'title' => 'fae'.$i,
                'color' => '#1f1f1F',  
            ); 
        }
        //create the tags.
        for ($i = 0; $i < 31; $i++) {
            $response[$i] = $this->call('post', 'http://'.$this->domain.'/tags', $parameters[$i], [], [], $this->transformHeadersToServerVars($server2));   
            $this->refreshApplication();
        }     
        $parameters1 = array(
            'page' => 1
        );
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/tags', $parameters1, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response_page1->getContent()); 
        for ($i = 0; $i < 30; $i++) {
            $this->seeJson([
                    'tag_id' => $array2[$i]->tag_id,
                    'title' => $array2[$i]->title,
                    'color' => '#1f1f1F'
            ]);
        }
        $result = false;
        if ($response_page1->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);
        $this->refreshApplication();
        $parameters2 = array(
            'page' => 2
        );
        $response_page2 = $this->call('get', 'http://'.$this->domain.'/tags', $parameters2, [], [], $this->transformHeadersToServerVars($server2));   
        $array3 = json_decode($response_page2->getContent());  
        $this->seeJson([
                    'tag_id' => 10,
                    'title' => 'fae9',
                    'color' => '#1f1f1F'
        ]);
        $result = false;
        if ($response_page2->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the response when the input format is wrong.
    public function testGetArray2() {
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
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array();
        $response = array();
        $tag = array();
        for ($i = 0; $i < 31; $i++) {
            $parameters[$i] = array(
                'title' => 'fae'.$i,
                'color' => '#1f1f1F',  
            ); 
        }
        //create the tags.
        for ($i = 0; $i < 31; $i++) {
            $response[$i] = $this->call('post', 'http://'.$this->domain.'/tags', $parameters[$i], [], [], $this->transformHeadersToServerVars($server2));   
            $this->refreshApplication();
        }     
        //the format if the page is wrong.
        $parameters1 = array(
            'page' => 'fae'
        );
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/tags', $parameters1, [], [], $this->transformHeadersToServerVars($server2));   
        var_dump($response_page1);
        $array2 = json_decode($response_page1->getContent()); 
        $result = false;
        if ($response_page1->status() == '422' && $array2->message = 'Could not get array.' && $array2->errors->page[0] == 'The page must be an integer.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test correct response of the method of getOne.
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
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array();
        $response = array();
        $tag = array();
        for ($i = 0; $i < 31; $i++) {
            $parameters[$i] = array(
                'title' => 'fae'.$i,
                'color' => '#1f1f1F',  
            ); 
        }
        //create the tags.
        for ($i = 0; $i < 31; $i++) {
            $response[$i] = $this->call('post', 'http://'.$this->domain.'/tags', $parameters[$i], [], [], $this->transformHeadersToServerVars($server2));   
            $this->refreshApplication();
        }   
        $response = $this->call('get', 'http://'.$this->domain.'/tags/1', [], [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->seeJson([
                    'tag_id' => 1,
                    'title' => 'fae0',
                    'color' => '#1f1f1F'
        ]);
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the response when the tag with the tag_id is null.
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
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array();
        $response = array();
        $tag = array();
        for ($i = 0; $i < 31; $i++) {
            $parameters[$i] = array(
                'title' => 'fae'.$i,
                'color' => '#1f1f1F',  
            ); 
        }
        //create the tags.
        for ($i = 0; $i < 31; $i++) {
            $response[$i] = $this->call('post', 'http://'.$this->domain.'/tags', $parameters[$i], [], [], $this->transformHeadersToServerVars($server2));   
            $this->refreshApplication();
        }   
        $response = $this->call('get', 'http://'.$this->domain.'/tags/33', [], [], [], $this->transformHeadersToServerVars($server2));$this->seeJson([
                 'message' => 'tag not found',
                 'error_code' => '404-4',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    //test the response when the format of the tag_id is wrong.
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
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array();
        $response = array();
        $tag = array();
        for ($i = 0; $i < 31; $i++) {
            $parameters[$i] = array(
                'title' => 'fae'.$i,
                'color' => '#1f1f1F',  
            ); 
        }
        //create the tags.
        for ($i = 0; $i < 31; $i++) {
            $response[$i] = $this->call('post', 'http://'.$this->domain.'/tags', $parameters[$i], [], [], $this->transformHeadersToServerVars($server2));   
            $this->refreshApplication();
        }   
        $response = $this->call('get', 'http://'.$this->domain.'/tags/fae', [], [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    //test correct response of the method of getAllPins. 
    public function testGetAllPins() {
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
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'title' => 'fae',
            'color' => '#1f1f1F',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/tags', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
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

        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 32; $i++) { 
            ${'parameters' . $i} = array(
                'file_ids' => '1;2',
                'tag_ids' => '1',
                'description' => 'This is the test'.$i,
                'geo_longitude' => $longitude,
                'geo_latitude' => $latitude, 
                'duration' => '1440',
            ); 
            $response = $this->call('post', 'http://'.$this->domain.'/medias', ${'parameters' . $i}, [], [], $this->transformHeadersToServerVars($server2));   
            $latitude = number_format($latitude + 0.000001, 6); 
            $this->refreshApplication();
        }  
        $parameters = array(
            'page' => 1,
        );
        $response1= $this->call('get', 'http://'.$this->domain.'/tags/1/pin', $parameters, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response1->getContent());  
        for ($i = 0; $i < 30; $i++) {
            $this->seeJson([  
                    'pin_id' => $array2[$i]->pin_id,
                    'type' => 'media',
            ]);
        }
        $this->refreshApplication();
        $content = array( 
            'page' => 2,
        ); 
        $response2 = $this->call('get', 'http://'.$this->domain.'/tags/1/pin', $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $array3 = json_decode($response2->getContent()); 
        $this->seeJson([  
                    'pin_id' => $array3[0]->pin_id,
                    'type' => 'media',
        ]);
        $result = false;
        if ($response1->headers->get('page') == '1' && $response1->headers->get('total-pages') == '2' && $response1->status() == '200') {
            $result = true;
        }  
    }
    //test the response when the format of the tag_id is wrong. 
    public function testGetAllPins2() {
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
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'title' => 'fae',
            'color' => '#1f1f1F',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/tags', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
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

        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 33; $i++) { 
            ${'parameters' . $i} = array(
                'file_ids' => '1;2',
                'tag_ids' => '1',
                'description' => 'This is the test'.$i,
                'geo_longitude' => $longitude,
                'geo_latitude' => $latitude, 
                'duration' => '1440',
            ); 
            $response = $this->call('post', 'http://'.$this->domain.'/medias', ${'parameters' . $i}, [], [], $this->transformHeadersToServerVars($server2));   
            $latitude = number_format($latitude + 0.000001, 6); 
            $this->refreshApplication();
        }  
        $parameters = array(
            'page' => 1,
        );
        $response = $this->call('get', 'http://'.$this->domain.'/tags/fae/pin', $parameters, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    //test the response when tag does not exist. 
    public function testGetAllPins3() {
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

        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 33; $i++) { 
            ${'parameters' . $i} = array(
                'file_ids' => '1;2',
                'tag_ids' => '1',
                'description' => 'This is the test'.$i,
                'geo_longitude' => $longitude,
                'geo_latitude' => $latitude, 
                'duration' => '1440',
            ); 
            $response = $this->call('post', 'http://'.$this->domain.'/medias', ${'parameters' . $i}, [], [], $this->transformHeadersToServerVars($server2));   
            $latitude = number_format($latitude + 0.000001, 6); 
            $this->refreshApplication();
        }  
        $parameters = array(
            'page' => 1,
        );
        $response = $this->call('get', 'http://'.$this->domain.'/tags/1/pin', $parameters, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'tag not found',
                 'error_code' => '404-4',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    //test the response when the input format is wrong. 
    public function testGetAllPins4() {
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
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );    
        $parameters = array(
            'title' => 'fae',
            'color' => '#1f1f1F',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/tags', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
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

        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 33; $i++) { 
            ${'parameters' . $i} = array(
                'file_ids' => '1;2',
                'tag_ids' => '1',
                'description' => 'This is the test'.$i,
                'geo_longitude' => $longitude,
                'geo_latitude' => $latitude, 
                'duration' => '1440',
            ); 
            $response = $this->call('post', 'http://'.$this->domain.'/medias', ${'parameters' . $i}, [], [], $this->transformHeadersToServerVars($server2));   
            $latitude = number_format($latitude + 0.000001, 6); 
            $this->refreshApplication();
        }  
        $parameters = array(
            'page' => 'wrong',
        );
        $response = $this->call('get', 'http://'.$this->domain.'/tags/1/pin', $parameters, [], [], $this->transformHeadersToServerVars($server2));  
        $array2 = json_decode($response->getContent());  
        $result = false;
        if ($response->status() == '422' && $array2->message = 'Could not get user pins.' && $array2->errors->page[0] == 'The page must be an integer.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
}
