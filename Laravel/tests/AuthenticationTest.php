<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Dingo\Api\Routing\Helpers;
use App\Users;
 

//you should first test login and then comment the method of testLogin and test logout
class AuthenticationTest extends TestCase {
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
        // $this->markTestSkipped();  
    } 

    public function tearDown() {
        parent::tearDown();
    }

    // test the login is successful.
    public function testLogin() { 
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
        $response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($response->getContent());
        $this->seeJson([
                 'user_id' => $array->user_id,
                 'token' => $array->token,
                 'session_id' => $array->session_id,
                 'debug_base64ed' => $array->debug_base64ed,
        ]);
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the input format of the contents!
    public function testLogin1() { 
        $parameters = array(
            'email' => 'letsfae126.com', // no @ in the email;
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
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
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
        //this user information does not exist!
        $parameters = array(
            'email' => 'letsfae@yahoo.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array->message == 'Bad request, No such users exist!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //to test whether the togin time is more than 3! 
    public function testLogin3() { 
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02', 
            'login_count' => 3,
        ]);
        //login_count is more than 3 times.
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaefalse',
            'user_name' => 'kevin',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array->message == 'You have tried to login 3 times, please change your password!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //to test whether the password is wrong! 
    public function testLogin4() { 
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02', 
             'login_count' => 0,
        ]);
        //login_count is more than 3 times.
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaefalse',
            'user_name' => 'kevin',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
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
            //wrong input header! 
            'Fae-Client-Version' => 'ios-0.0.1_limit_over_50_limit_over_50_limit_over_50_limit_over_50_limit_over_50_limit_over_50_limit_over_50_limit_over_50', 
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array->message == 'Bad request, Please verify your input header!') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    public function testLogout() { 
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
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $servers2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Device-ID' => 'gu3v0KaU7jLS7SGdS2Rb',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $logout_response = $this->call('delete', 'http://'.$this->domain.'/authentication', [], [], [], $this->transformHeadersToServerVars($servers2));
        $this->assertResponseStatus(204);
    }
}

