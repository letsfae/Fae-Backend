<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Users;
use App\Sessions;
 

//you should first test login and then comment the method of testLogin and test logout
class AuthenticationTest extends TestCase {
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
        parent::tearDown();
        $this->beforeApplicationDestroyed(function () {
            DB::disconnect();
        });
    }

    // test the login is successful.
    public function testLogin() { 
        // $this->markTestSkipped();  
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
        $response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($response->getContent());
        $this->seeJson([
                 'user_id' => $array->user_id,
                 'token' => $array->token,
                 'session_id' => $array->session_id,
                 'debug_base64ed' => $array->debug_base64ed,
        ]);
        $session = Sessions::where('user_id', '=', 1)->first();
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
        $this->seeInDatabase('sessions', ['token' => $session->token, 'is_mobile' => false, 'device_id' => null, 'client_version' => 'ios-0.0.1']);
    }

    //test the input format of the contents!
    public function testLogin1() {  
        // $this->markTestSkipped(); 
        $parameters = array(
            'email' => 'letsfae126.com', // no @ in the email;
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',  
        );
        $response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array->message == 'Bad request, Please verify your information!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the user exists
    public function testLogin2() {  
        // $this->markTestSkipped(); 
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
        //this user information does not exist!
        $parameters = array(
            'email' => 'letsfae@yahoo.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',  
        );
        $response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array->message == 'Bad request, No such users exist!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //to test whether the togin time is more than 6! 
    public function testLogin3() { 
        // $this->markTestSkipped();  
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02', 
            'login_count' => 6,
        ]);
        //login_count is more than 3 times.
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaefalse',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',  
        );
        $response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array->message == 'You have tried to login 6 times, please change your password!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //to test whether the password is wrong! 
    public function testLogin4() {  
        // $this->markTestSkipped(); 
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
        //login_count is more than 3 times.
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaefalse',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',  
        );
        $response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array->message == 'Bad request, Password incorrect!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

   // test whether the input format of the header is right!
    public function testLogin5() {  
        // $this->markTestSkipped(); 
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
            //wrong input header! 
            'Fae-Client-Version' => 'ios-0.0.1_limit_over_50_limit_over_50_limit_over_50_limit_over_50_limit_over_50_limit_over_50_limit_over_50_limit_over_50', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array->message == 'Bad request, Please verify your input header!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test whether the format of device_id is right!
    public function testLogin6() {  
        // $this->markTestSkipped(); 
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
            //wrong format of device_id.
            'device_id' => 'gu3v0KaU7jLS7SGdS2Rbgu3v0KaU7jLS7SGdS2Rbgu3v0KaU7jLS7SGdS2Rbgu3v0KaU7jLS7SGdS2Rbgu3v0KaU7jLS7SGdS2Rbgu3v0KaU7jLS7SGdS2Rbgu3v0KaU7jLS7SGdS2Rbgu3v0KaU7jLS7SGdS2Rb',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array->message == 'Bad request, device id is wrong!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test whether the format of is_mobile is right!
    public function testLogin7() { 
        // $this->markTestSkipped();  
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
            //wrong format of is_mobile.
            'is_mobile' => 'false1', 
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array->message == 'Bad request, is_mobile is wrong!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the response when the input is_mobile is true , which is the same as the is_mobile in the database.
    public function testLogin8() {  
        // $this->markTestSkipped(); 
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
        $parameters1 = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',  
            'is_mobile' => 'true',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameters2 = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp', 
            'is_mobile' => 'true', 
            'device_id' => 'gu3v0KaU7jLS7SGdS2Rb'
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters2, [], [], $this->transformHeadersToServerVars($server));
        $session = Sessions::where('user_id', '=', 1)->first();
        $array = json_decode($response->getContent()); 
        $this->seeInDatabase('sessions', ['token' => $session->token, 'is_mobile' => true, 'device_id' => 'gu3v0KaU7jLS7SGdS2Rb', 'client_version' => 'ios-0.0.1']);
    }

    public function testLogout() { 
        // $this->markTestSkipped(); 
        $parameter = array(
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter, [], [], $this->transformHeadersToServerVars($server));
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
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $array = json_decode($login_response->getContent());
        $servers2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $logout_response = $this->call('delete', 'http://'.$this->domain.'/authentication', [], [], [], $this->transformHeadersToServerVars($servers2));
        $this->assertResponseStatus(204);
    }
}

