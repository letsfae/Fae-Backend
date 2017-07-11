<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Users;
use App\Verifications;

class resetLoginTest extends TestCase {
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
        $this->testEmail = getenv('Test_Email'); 
        $this->markTestSkipped(); 
    } 

    public function tearDown() {
        parent::tearDown();
    }

    //test the correct response of the method of sendResetCode.
    public function testSendResetCode() {
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
            'email' => $this->testEmail,
            'password' => 'letsfaego',
            'first_name' => 'kevin2',
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
        $this->refreshApplication();
        $parameters2 = array(
            'email' => $this->testEmail,
        );
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/reset_login/code', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $verification = Verifications::where('email','=', $this->testEmail)->first();
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
        $this->seeInDatabase('verifications', ['type' => 'resetpassword', 'email' => $this->testEmail, 'code' => $verification->code]);
    }  

    //test whether the input format is correct.
    public function testSendResetCode2() {
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
            'email' => $this->testEmail,
            'password' => 'letsfaego',
            'first_name' => 'kevin2',
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
        $this->refreshApplication();
        //no input.
        $parameters2 = array( 
        );
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/reset_login/code', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent());  
        $result = false; 
        if ($response->status() == '422' && $array2->message == 'Could not verify.' && $array2->errors->email[0] == 'The email field is required.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test whether the user exists in the database.
    public function testSendResetCode3() {
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
        $this->refreshApplication(); 
        $parameters2 = array(
            'email' => $this->testEmail,
        );
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        $response = $this->call('post', 'http://'.$this->domain.'/reset_login/code', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
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

    //test how the database of verifications changed after the code existing more than 30 minitues.
    public function testSendResetCode4() {
        $this->markTestSkipped(); 
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
        $parameter2 = array(
            'email' => $this->testEmail,
            'password' => 'letsfaego',
            'first_name' => 'kevin2',
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
        $this->refreshApplication();
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        ); 
        $parameters2 = array(
            'email' => $this->testEmail,
        );
        $verification = Verifications::create([
            'type' => 'resetpassword',
            'email' => $this->testEmail,
            'code' => '-10000',
            'created_at' => '2016-07-10 01:57:33'
        ]);
        $response = $this->call('post', 'http://'.$this->domain.'/reset_login/code', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent()); 
        $verifications = Verifications::where('email','=', $this->testEmail)->first();
        $result = false;
        if ($verifications->code != '-10000') {
            $result = true;
        }  
        $this->assertTrue($result);
    }

    //test correct response of the method of VerifyResetCode.
    public function testVerifyResetCode() {
        $this->markTestSkipped(); 
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
        $parameter2 = array(
            'email' => $this->testEmail,
            'password' => 'letsfaego',
            'first_name' => 'kevin2',
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
        $this->refreshApplication();
        $array = json_decode($login_response->getContent());
        $verification = Verifications::create([
            'type' => 'resetpassword',
            'email' => $this->testEmail,
            'code' => '555555',
        ]);
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        ); 
        //verify email.
        $parameters2 = array(
            'email' => $this->testEmail,
            'code' => '555555'
        );
        $response = $this->call('post', 'http://'.$this->domain.'/reset_login/code/verify', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent());  
        $result = false; 
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
    }

    //test whether the input format is right.
    public function testVerifyResetCode2() {
        $this->markTestSkipped(); 
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
        $parameter2 = array(
            'email' => $this->testEmail,
            'password' => 'letsfaego',
            'first_name' => 'kevin2',
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
        $this->refreshApplication();
        $array = json_decode($login_response->getContent());
        $verification = Verifications::create([
            'type' => 'resetpassword',
            'email' => $this->testEmail,
            'code' => '555555',
        ]);
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        ); 
        //wrong format of the code.
        $parameters2 = array(
            'email' => $this->testEmail,
            'code' => '55555555'
        );
        $response = $this->call('post', 'http://'.$this->domain.'/reset_login/code/verify', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent()); 
        $result = false; 
        if ($response->status() == '422' && $array2->message == 'Could not verify.' && $array2->errors->code[0] == 'The code may not be greater than 6 characters.') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test whether the data of the verification exists.
    public function testVerifyResetCode3() {
        $this->markTestSkipped(); 
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
        $parameter2 = array(
            'email' => $this->testEmail,
            'password' => 'letsfaego',
            'first_name' => 'kevin2',
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
        $this->refreshApplication();
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        ); 
        $parameters2 = array(
            'email' => $this->testEmail,
            'code' => '555555'
        );
        $response = $this->call('post', 'http://'.$this->domain.'/reset_login/code/verify', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'verification not found',
                 'error_code' => '404-14',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the response when the input code is not the same as the code in verifications table.
    public function testVerifyResetCode4() {
        $this->markTestSkipped(); 
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
        $parameter2 = array(
            'email' => $this->testEmail,
            'password' => 'letsfaego',
            'first_name' => 'kevin2',
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
        $this->refreshApplication();
        $array = json_decode($login_response->getContent());
        $verification = Verifications::create([
            'type' => 'resetpassword',
            'email' => $this->testEmail,
            'code' => '555555',
        ]);
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        ); 
        //the code is not the same as the code in the database.
        $parameters2 = array(
            'email' => $this->testEmail,
            'code' => '555556'
        );
        $response = $this->call('post', 'http://'.$this->domain.'/reset_login/code/verify', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'verification code is wrong',
                 'error_code' => '403-5',
                 'status_code' => '403', 
        ]); 
        $result = false;
        if ($response->status() == '403') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test what is the response when the data has created more than 30 minitues in the verifications table.
    public function testVerifyResetCode5() {
        $this->markTestSkipped(); 
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
        $parameter2 = array(
            'email' => $this->testEmail,
            'password' => 'letsfaego',
            'first_name' => 'kevin2',
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
        $this->refreshApplication();
        $array = json_decode($login_response->getContent());
        $verification = Verifications::create([
            'type' => 'resetpassword',
            'email' => $this->testEmail,
            'code' => '555555',
            'created_at' => '2016-07-10 01:57:33'
        ]);
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );  
        $parameters2 = array(
            'email' => $this->testEmail,
            'code' => '555555'
        );
        $response = $this->call('post', 'http://'.$this->domain.'/reset_login/code/verify', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'verification timeout',
                 'error_code' => '403-4',
                 'status_code' => '403', 
        ]); 
        $result = false;
        if ($response->status() == '403') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test correct response of the method of resetPassword.
    public function testResetPassword() {
        $this->markTestSkipped(); 
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
        $parameter2 = array(
            'email' => $this->testEmail,
            'password' => 'letsfaego',
            'first_name' => 'kevin2',
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
        $this->refreshApplication();
        $array = json_decode($login_response->getContent());
        $verification = Verifications::create([
            'type' => 'resetpassword',
            'email' => $this->testEmail,
            'code' => '555555',
        ]);
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        ); 
        //verify email.
        $parameters2 = array(
            'email' => $this->testEmail,
            'code' => '555555',
            'password' => 'updateletsfaego'
        );
        $response = $this->call('post', 'http://'.$this->domain.'/reset_login/password', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent()); 
        $result = false; 
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
        $this->seeInDatabase('users', ['email' => $this->testEmail, 'login_count' => 0]);
    }

    //test whether the input format is right.
    public function testResetPassword2() {
        $this->markTestSkipped(); 
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
        $parameter2 = array(
            'email' => $this->testEmail,
            'password' => 'letsfaego',
            'first_name' => 'kevin2',
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
        $this->refreshApplication();
        $array = json_decode($login_response->getContent());
        $verification = Verifications::create([
            'type' => 'resetpassword',
            'email' => $this->testEmail,
            'code' => '555555',
        ]);
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        ); 
        //wrong format of the code.
        $parameters2 = array(
            'email' => $this->testEmail,
            'code' => '55555555',
            'password' => 'updateletsfaego'
        );
        $response = $this->call('post', 'http://'.$this->domain.'/reset_login/password', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent());
        $result = false; 
        if ($response->status() == '422' && $array2->message == 'Could not reset.' && $array2->errors->code[0] == 'The code may not be greater than 6 characters.') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test whether the data of the verification exists.
    public function testResetPassword3() {
        $this->markTestSkipped(); 
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
        $parameter2 = array(
            'email' => $this->testEmail,
            'password' => 'letsfaego',
            'first_name' => 'kevin2',
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
        $this->refreshApplication();
        $array = json_decode($login_response->getContent()); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );  
        $parameters2 = array(
            'email' => $this->testEmail,
            'code' => '555555',
            'password' => 'updateletsfaego'
        );
        $response = $this->call('post', 'http://'.$this->domain.'/reset_login/password', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'verification not found',
                 'error_code' => '404-14',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the response when the input code is not the same as the code in verifications table.
    public function testResetPassword4() {
        $this->markTestSkipped(); 
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
        $parameter2 = array(
            'email' => $this->testEmail,
            'password' => 'letsfaego',
            'first_name' => 'kevin2',
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
        $this->refreshApplication();
        $array = json_decode($login_response->getContent()); 
        $verification = Verifications::create([
            'type' => 'resetpassword',
            'email' => $this->testEmail,
            'code' => '555555',
        ]);
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        ); 
        //the code is not the same as the code in the database.
        $parameters2 = array(
            'email' => $this->testEmail,
            'code' => '555556',
            'password' => 'updateletsfaego'
        );
        $response = $this->call('post', 'http://'.$this->domain.'/reset_login/password', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'verification code is wrong',
                 'error_code' => '403-5',
                 'status_code' => '403', 
        ]); 
        $result = false;
        if ($response->status() == '403') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test what is the response when the data has created more than 30 minitues in the verifications table.
    public function testResetPassword5() {
        $this->markTestSkipped(); 
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
        $parameter2 = array(
            'email' => $this->testEmail,
            'password' => 'letsfaego',
            'first_name' => 'kevin2',
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
        $this->refreshApplication();
        $array = json_decode($login_response->getContent()); 
        $verification = Verifications::create([
            'type' => 'resetpassword',
            'email' => $this->testEmail,
            'code' => '555555',
            'created_at' => '2016-07-10 01:57:33'
        ]);
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );  
        $parameters2 = array(
            'email' => $this->testEmail,
            'code' => '555555',
            'password' => 'updateletsfaego'
        );
        $response = $this->call('post', 'http://'.$this->domain.'/reset_login/password', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'verification timeout',
                 'error_code' => '403-4',
                 'status_code' => '403', 
        ]); 
        $result = false;
        if ($response->status() == '403') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }
}
