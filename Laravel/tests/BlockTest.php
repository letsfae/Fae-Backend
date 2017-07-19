<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Users;
use App\Sessions;
use App\Friends;

class BlockTest extends TestCase
{
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

    // the correct response of the add block.
    public function testAdd() { 
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

        $parameters2 = array(
            'user_id' => 2,
        ); 
        $friend1 = Friends::create([
            'user_id' => 1,
            'friend_id' => 2,
        ]);
        $friend2 = Friends::create([
            'user_id' => 2,
            'friend_id' => 1,
        ]);
        //create the add block.
        $response = $this->call('post', 'http://'.$this->domain.'/blocks', $parameters2, [], [], $this->transformHeadersToServerVars($server2));   
        $result = false; 
        if ($response->status() == '201') {
            $result = true;
        }  
        $this->assertEquals(true, $result);  
        $this->seeInDatabase('blocks', ['user_id' => 1, 'block_id' => 2]); 
    }
    // test the response when the user_id is the self_user_id.
    public function testAdd2() { 
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

        $parameters2 = array(
            'user_id' => 1,
        ); 
        $friend1 = Friends::create([
            'user_id' => 1,
            'friend_id' => 2,
        ]);
        $friend2 = Friends::create([
            'user_id' => 2,
            'friend_id' => 1,
        ]);
        //create the add block.
        $response = $this->call('post', 'http://'.$this->domain.'/blocks', $parameters2, [], [], $this->transformHeadersToServerVars($server2));    
        $this->seeJson([
                 'message' => 'Bad request, you can not block yourself!',
                 'error_code' => '400-16',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }
    // test the response when the requested_user_id is null.
    public function testAdd3() { 
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

        $parameters2 = array(
            'user_id' => 3,
        ); 
        $friend1 = Friends::create([
            'user_id' => 1,
            'friend_id' => 2,
        ]);
        $friend2 = Friends::create([
            'user_id' => 2,
            'friend_id' => 1,
        ]);
        //create the add block.
        $response = $this->call('post', 'http://'.$this->domain.'/blocks', $parameters2, [], [], $this->transformHeadersToServerVars($server2));    
        $this->seeJson([
                 'message' => 'request user not found',
                 'error_code' => '404-3',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }
    // the correct response of the add block.
    public function testDelete() { 
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

        $parameters2 = array(
            'user_id' => 2,
        ); 
        $friend1 = Friends::create([
            'user_id' => 1,
            'friend_id' => 2,
        ]);
        $friend2 = Friends::create([
            'user_id' => 2,
            'friend_id' => 1,
        ]);
        //create the add block.
        $response = $this->call('post', 'http://'.$this->domain.'/blocks', $parameters2, [], [], $this->transformHeadersToServerVars($server2));   
        $this->refreshApplication(); 
        $delete_response = $this->call('delete', 'http://'.$this->domain.'/blocks/2', [], [], [], $this->transformHeadersToServerVars($server2));    
        $result = false; 
        if ($delete_response->status() == '204') {
            $result = true;
        }  
        $this->assertEquals(true, $result);  
        $this->notSeeInDatabase('blocks', ['user_id' => 1, 'block_id' => 2]);
    }
    // test the response when the blocked_user_id is null.
    public function testDelete2() { 
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

        $parameters2 = array(
            'user_id' => 2,
        ); 
        $friend1 = Friends::create([
            'user_id' => 1,
            'friend_id' => 2,
        ]);
        $friend2 = Friends::create([
            'user_id' => 2,
            'friend_id' => 1,
        ]);
        //create the add block.
        $response = $this->call('post', 'http://'.$this->domain.'/blocks', $parameters2, [], [], $this->transformHeadersToServerVars($server2));   
        $this->refreshApplication(); 
        $delete_response = $this->call('delete', 'http://'.$this->domain.'/blocks/3', [], [], [], $this->transformHeadersToServerVars($server2));    
        $this->seeJson([
                 'message' => 'user not found',
                 'error_code' => '404-3',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($delete_response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
}

