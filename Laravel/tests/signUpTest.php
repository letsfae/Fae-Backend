<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Comments;
use App\Users;

class signUpTest extends TestCase {
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

    // the correct response of the register.
    public function testSignUp() {  
        $this->markTestSkipped(); 
        $parameters = array(
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test whether the format of the input is right.
    public function testSignUp2() { 
        $this->markTestSkipped(); 
        $parameters = array(
            'email' => 'letsfae126.com', //the email format is not right.
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '422' && $array->message == 'Could not create new user.' && $array->errors->email[0] == 'The email must be a valid email address.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the response when the user_name is null.
    public function testSignUp3() { 
        $this->markTestSkipped(); 
        $parameters = array(
            'email' => 'letsfae@126.com',  
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang', 
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($response->getContent());
        $result = false; 
        if ($response->status() == '422' && $array->message == 'Could not create new user.' && $array->errors->user_name[0] == 'The user name field is required.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    //test the response when the user_name exists.
    public function testSignUp4() { 
        $this->markTestSkipped(); 
        $parameters = array(
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameters, [], [], $this->transformHeadersToServerVars($server));
         $this->refreshApplication();
        $array = json_decode($response->getContent());
        $parameters2 = array(
            'email' => 'letsfae2@126.com',  
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang', 
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        ); 
        $response2 = $this->call('post', 'http://'.$this->domain.'/users', $parameters2, [], [], $this->transformHeadersToServerVars($server)); 
        $array2 = json_decode($response2->getContent());
        $result = false; 
        if ($response2->status() == '400' && $array2->message == 'user name already exists') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
}
