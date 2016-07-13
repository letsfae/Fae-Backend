<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing;
use App\Comments;
use App\Users;


class CommentTest extends TestCase {
    /**
     * A basic test example.
     *
     * @return void
     */
    use DatabaseMigrations;
    /** @test */
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

    // the correct response of the create comment.
    public function testCommentCanBeCreated() { 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters2 = array(
            'content' => 'This is the test.',
            'geo_longitude' => '-118.2799',
            'geo_latitude' => '34.2799', 
        ); 
        //create the comment.
        $response = $this->call('post', 'http://'.$this->domain.'/comments', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'comment_id' => json_decode($response->getContent())->comment_id,
        ]);
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
    }

    // to test whether the input format is right.
    public function testCommentCanBeCreated2() {
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters2 = array(
            'content' => 'This is the test.',
            'geo_longitude' => '-118.2799',
            'geo_latitude' => '-118.99', //the wrong format of the latitude
        ); 
        //create the comment.
        $response = $this->call('post', 'http://'.$this->domain.'/comments', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array2->message == 'Bad request, Please verify your input!') {
            $result = true;
        }
        $this->assertEquals(true, $result);   
    }

    // the correct response of the get comment with the comment_id.
    public function testCommentCanBeGot() {
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters2 = array(
            'content' => 'This is the test.',
            'geo_longitude' => '-118.2799',
            'geo_latitude' => '34.2799', 
        ); 
        //create the comment.
        $response2 = $this->call('post', 'http://'.$this->domain.'/comments', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response2->getContent());
        //get the comment
        $response = $this->call('get', 'http://'.$this->domain.'/comments/'.$array2->comment_id, [], [], [], $this->transformHeadersToServerVars($server2)); 
        $array3 = json_decode($response->getContent());
        $this->seeJson([
                'comment_id' => $array3->comment_id,
                'user_id' => $array3->user_id,
                'content' => $array3->content,
                'geolocation' => array(
                    'latitude' => $array3->geolocation->latitude,
                    'longitude' => $array3->geolocation->longitude,
                ),
                'created_at' => $array3->created_at,
        ]);
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test whether the format of the comment_id is valid.
    public function testCommentCanBeGot2() {
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        //the format of the comment_id is not valid.
        //get the comment.
        $response = $this->call('get', 'http://'.$this->domain.'/comments/letsfae', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array2->message == 'Bad request, Please type the correct comment_id format!') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    // the comment with the given comment_id does not exist.
    public function testCommentCanBeGot3() { 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters2 = array(
            'content' => 'This is the test.',
            'geo_longitude' => '-118.2799',
            'geo_latitude' => '34.2799', 
        ); 
        //create the comment.
        $response2 = $this->call('post', 'http://'.$this->domain.'/comments', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response2->getContent());
        //test the comment with the comment_id -1 does not exist!
        //get the comment
        $response = $this->call('get', 'http://'.$this->domain.'/comments/-1'.$array2->comment_id, [], [], [], $this->transformHeadersToServerVars($server2)); 
        $array3 = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array3->message == 'Bad request, No such comments exist!') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
    }

    // the correct response of the method of getting all comments of the given user.
    public function testUserCommentsCanBeGot() { 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters = array();
        $response = array();
        for ($i = 0; $i < 31; $i++) {
            $parameters[$i] = array(
                'content' => 'This is the test'.$i,
                'geo_longitude' => '-118.2799',
                'geo_latitude' => '34.2799', 
            );
        }
        //create the comments.
        for ($i = 0; $i < 31; $i++) {
            $response[$i] = $this->call('post', 'http://'.$this->domain.'/comments', $parameters[$i], [], [], $this->transformHeadersToServerVars($server2)); 
            // sleep(1);
            $this->refreshApplication();
        } 
        $content = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        );
        //get the comments of the user with the user_id.
        //get the comments of the page 1.
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/comments/users/'.$array->user_id, $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response_page1->getContent());
        for ($i = 0; $i < 30; $i++) {
            $this->seeJson([
                    'page' => $array2[$i]->page,
                    'total_pages' => $array2[$i]->total_pages,
                    'comments' => array(
                        'comment_id' => $array2[$i]->comments->comment_id,
                        'user_id' => $array2[$i]->comments->user_id,
                        'content' => $array2[$i]->comments->content,
                        'geolocation' => array(
                            'latitude' => $array2[$i]->comments->geolocation->latitude,
                            'longitude' => $array2[$i]->comments->geolocation->longitude,
                        ),
                        'created_at' => $array2[$i]->comments->created_at,
                    ),
            ]);
        }
        $this->refreshApplication();
        $content2 = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 2,
        );
        //get the comments of the page 2.
        $response_page2 = $this->call('get', 'http://'.$this->domain.'/comments/users/'.$array->user_id, $content2, [], [], $this->transformHeadersToServerVars($server2)); 
        $array3 = json_decode($response_page2->getContent());
        $this->seeJson([
                'page' => $array3[0]->page,
                'total_pages' => $array3[0]->total_pages,
                'comments' => array(
                    'comment_id' => $array3[0]->comments->comment_id,
                    'user_id' => $array3[0]->comments->user_id,
                    'content' => $array3[0]->comments->content,
                    'geolocation' => array(
                        'latitude' => $array3[0]->comments->geolocation->latitude,
                        'longitude' => $array3[0]->comments->geolocation->longitude,
                    ),
                    'created_at' => $array3[0]->comments->created_at,
                ),
        ]);
        $result = false;
        if ($response_page1->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);

    }

    //test whether the format of the user_id is valid.
    public function testUserCommentsCanBeGot2() { 
        // $this->markTestSkipped();
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        //the format of the user_id is not valid.
        //get the comment.
        $response = $this->call('get', 'http://'.$this->domain.'/comments/users/letfae', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array2->message == 'Bad request, Please type the correct user_id format!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    ////test whether the format of the input is valid.
    public function testUserCommentsCanBeGot3() {  
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        //get the comment.
        //the input of the start_time is not valid.
        $content = array(
            'start_time' => '2016-06-08 21:22:3',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        );
        $response = $this->call('get', 'http://'.$this->domain.'/comments/users/'.$array->user_id, $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array2->message == 'Bad request, Please verify your input!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the comments with the given user_id do not exist.
    public function testUserCommentsCanBeGot4() {  
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $this->refreshApplication();
        $parameters2 = array(
            'content' => 'This is the test.',
            'geo_longitude' => '-118.2799',
            'geo_latitude' => '34.2799', 
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/comments', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();
        //get the comment.
        $content = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        );
        //test the comments with the user_id -1 does not exist!
        $response = $this->call('get', 'http://'.$this->domain.'/comments/users/-1', $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array2->message == 'Bad request, No such comment exists!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the select page is larger than the total page.
    public function testUserCommentsCanBeGot5() {  
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        //create the comment.
        $parameters2 = array(
            'content' => 'This is the test.',
            'geo_longitude' => '-118.2799',
            'geo_latitude' => '34.2799', 
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/comments', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        //get the comment.
        $this->refreshApplication();
        $content = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 2,//the page 2 does not exist!
        );
        $response = $this->call('get', 'http://'.$this->domain.'/comments/users/'.$array->user_id, $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array2->message == 'Bad request, Please select the correct page!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the correct response of deleting of the comment with the given comment_id.
    public function testCommentCanBeDeleted() { 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        //create the comment.
        $parameters2 = array(
            'content' => 'This is the test.',
            'geo_longitude' => '-118.2799',
            'geo_latitude' => '34.2799', 
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/comments', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response2->getContent());
        $this->refreshApplication();
        //delete the comment with the comment_id.
        $response = $this->call('delete', 'http://'.$this->domain.'/comments/'.$array2->comment_id, $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->assertResponseStatus(204);
    }

    //test whether the format of the given comment_id is valid.
    public function testCommentCanBeDeleted2() { 
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        //create the comment.
        $parameters2 = array(
            'content' => 'This is the test.',
            'geo_longitude' => '-118.2799',
            'geo_latitude' => '34.2799', 
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/comments', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response2->getContent());
        $this->refreshApplication();
        //the format of the comment_id is not valid.
        $response = $this->call('delete', 'http://'.$this->domain.'/comments/letsfae', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $array3 = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array3->message == 'Bad request, Please type the correct comment_id format!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the comment with the given comment_id does not exist.
    public function testCommentCanBeDeleted3() {  
        //register of the user.
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        //create the comment.
        $parameters2 = array(
            'content' => 'This is the test.',
            'geo_longitude' => '-118.2799',
            'geo_latitude' => '34.2799', 
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/comments', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response2->getContent());
        $this->refreshApplication();
        //the comment with the given comment_id does not exist.
        $response = $this->call('delete', 'http://'.$this->domain.'/comments/-1', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $array3 = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '422' && $array3->message == 'Delete failed') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
}
