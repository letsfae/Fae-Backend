<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Comments;
use App\Users;
use App\Name_cards;
use App\User_exts;
use App\Chats;

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
        $this->seeInDatabase('users', ['email' => 'letsfae@126.com', 'user_name' => 'faeapp', 'first_name' => 'kevin', 'last_name' => 'zhang', 'gender' => 'male', 'birthday' => '1992-02-02']);
        $this->seeInDatabase('user_exts', ['user_id' => 1]);
        $this->seeInDatabase('name_cards', ['user_id' => 1]);
        $this->seeInDatabase('chats', ['user_a_id' => 1, 'user_b_id' => 1, 'last_message' => 'Hey there! Welcome to Fae Map! Super happy to see you here. We’re here to
                               enhance your experience on Fae Map and make your time more fun. Let us know
                               of any problems you encounter or what we can do to make your experience better. 
                               We’ll be hitting you up with surprises, recommendations, favorite places, cool 
                               deals, and tons of fun stuff. Feel free to chat with us here anytime about 
                               anything. Let’s Fae!', 'last_message_type' => 'text', 'last_message_sender_id' => 1, 'user_b_unread_count' => 1]);
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
        $this->seeJson([
                 'message' => 'user name already exists',
                 'error_code' => '422-2',
                 'status_code' => '422', 
        ]); 
        $result = false;
        if ($response2->status() == '422') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
}
