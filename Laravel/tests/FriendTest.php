<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing;
use App\Friend_requests;
use App\Friends;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\Geometry;
use App\Users; 
use App\Sessions; 
use App\Blocks;

class FriendTest extends TestCase
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

    // the correct response of the requestFriend.
    public function testRequestFriend() { 
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
            'requested_user_id' => 2,
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/friends/request', $parameters2, [], [], $this->transformHeadersToServerVars($server2));   
        $result = false; 
        if ($response->status() == '201') {
            $result = true;
        }  
        $this->assertEquals(true, $result);  
        $this->seeInDatabase('friend_requests', ['user_id' => 1, 'requested_user_id' => 2]); 
    }

    // check the response when the input format is wrong.
    public function testRequestFriend2() { 
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
            'requested_user_id' => 'wrong',
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/friends/request', $parameters2, [], [], $this->transformHeadersToServerVars($server2));  
        $array2 = json_decode($response->getContent());  
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Could not request friend.') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
    }

    // check the response when the requested_user is null
    public function testRequestFriend3() { 
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
            'requested_user_id' => 2,
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/friends/request', $parameters2, [], [], $this->transformHeadersToServerVars($server2));  
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
    // check the response when the requested_user is blocked by the user
    public function testRequestFriend4() { 
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
        $block = Blocks::create([
            'user_id' => 1,
            'block_id' => 2,
        ]);
        $parameters2 = array(
            'requested_user_id' => 2,
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/friends/request', $parameters2, [], [], $this->transformHeadersToServerVars($server2));  
        $this->seeJson([
                 'message' => 'Bad request, you have already blocked the user!',
                 'error_code' => '400-6',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
    }
    // the correct response of the acceptFriend.
    public function testAcceptFriend() { 
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
        $parameter = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters2 = array(
            'requested_user_id' => 2,
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/friends/request', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication(); 
        $parameter2 = array(
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response2 = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter2, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response2->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters3 = array(
            'friend_request_id' => 1,
        ); 
        $response2 = $this->call('post', 'http://'.$this->domain.'/friends/accept', $parameters3, [], [], $this->transformHeadersToServerVars($server2));  
        $result = false; 
        if ($response2->status() == '201') {
            $result = true;
        }  
        $this->assertEquals(true, $result);  
        $this->seeInDatabase('friends', ['user_id' => 1, 'friend_id' => 2]); 
        $this->seeInDatabase('friends', ['user_id' => 2, 'friend_id' => 1]); 
        $this->notSeeInDatabase('friend_requests', ['user_id' => 1, 'requested_user_id' => 2]);
    }
    // test the response when the input format is wrong..
    public function testAcceptFriend2() { 
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
        $parameter = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters2 = array(
            'requested_user_id' => 2,
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/friends/request', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication();
        $parameter2 = array(
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response2 = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter2, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response2->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters3 = array(
            'friend_request_id' => 'wrong',
        ); 
        $response2 = $this->call('post', 'http://'.$this->domain.'/friends/accept', $parameters3, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response2->getContent());  
        $result = false;
        if ($response2->status() == '422' && $array2->message == 'Could not accept friend request.') {
            $result = true;
        }
        $this->assertEquals(true, $result);    
    }
    // test the response when the friend request does not exist.
    public function testAcceptFriend3() { 
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
        $parameter = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters2 = array(
            'requested_user_id' => 2,
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/friends/request', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication();
        $parameter2 = array(
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response2 = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter2, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response2->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $parameters3 = array(
            'friend_request_id' => 2,
        ); 
        $response2 = $this->call('post', 'http://'.$this->domain.'/friends/accept', $parameters3, [], [], $this->transformHeadersToServerVars($server2));  
        $this->seeJson([
                 'message' => 'friend request not found',
                 'error_code' => '404-12',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response2->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // test the correct response of requested_user is blocked by the user.
    public function testAcceptFriend4() { 
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
        $parameter = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters2 = array(
            'requested_user_id' => 2,
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/friends/request', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication();
        $parameter2 = array(
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response2 = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter2, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response2->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        $block = Blocks::create([
            'user_id' => 2,
            'block_id' => 1,
        ]);
        $parameters3 = array(
            'friend_request_id' => 1,
        ); 
        $response2 = $this->call('post', 'http://'.$this->domain.'/friends/accept', $parameters3, [], [], $this->transformHeadersToServerVars($server2));  
        $this->seeJson([
                 'message' => 'Bad request, you have already blocked the user!',
                 'error_code' => '400-6',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response2->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);    
    }
    // the correct response of the ignoreFriend.
    public function testIgnoreFriend() { 
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
        $parameter = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters2 = array(
            'requested_user_id' => 2,
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/friends/request', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication(); 
        $parameter2 = array(
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response2 = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter2, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response2->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters3 = array(
            'friend_request_id' => 1,
        ); 
        $response2 = $this->call('post', 'http://'.$this->domain.'/friends/ignore', $parameters3, [], [], $this->transformHeadersToServerVars($server2));  
        $result = false; 
        if ($response2->status() == '201') {
            $result = true;
        }  
        $this->assertEquals(true, $result);   
        $this->notSeeInDatabase('friend_requests', ['user_id' => 1, 'requested_user_id' => 2]);
    }
    // test the response when the input format is wrong..
    public function testIgnoreFriend2() { 
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
        $parameter = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters2 = array(
            'requested_user_id' => 2,
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/friends/request', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication();
        $parameter2 = array(
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response2 = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter2, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response2->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters3 = array(
            'friend_request_id' => 'wrong',
        ); 
        $response2 = $this->call('post', 'http://'.$this->domain.'/friends/ignore', $parameters3, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response2->getContent());  
        $result = false;
        if ($response2->status() == '422' && $array2->message == 'Could not accept friend request.') {
            $result = true;
        }
        $this->assertEquals(true, $result);    
    }
    // test the response when the friend request does not exist.
    public function testIgnoreFriend3() { 
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
        $parameter = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters2 = array(
            'requested_user_id' => 2,
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/friends/request', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication();
        $parameter2 = array(
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response2 = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter2, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response2->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $parameters3 = array(
            'friend_request_id' => 2,
        ); 
        $response2 = $this->call('post', 'http://'.$this->domain.'/friends/ignore', $parameters3, [], [], $this->transformHeadersToServerVars($server2));  
        $this->seeJson([
                 'message' => 'friend request not found',
                 'error_code' => '404-12',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response2->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
    // the correct response of the deleteFriend.
    public function testDeleteFriend() { 
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
        $parameter = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters2 = array(
            'requested_user_id' => 2,
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/friends/request', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication(); 
        $parameter2 = array(
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response2 = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter2, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response2->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters3 = array(
            'friend_request_id' => 1,
        ); 
        $response2 = $this->call('post', 'http://'.$this->domain.'/friends/accept', $parameters3, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();  
        $response3 = $this->call('delete', 'http://'.$this->domain.'/friends/1', $parameters3, [], [], $this->transformHeadersToServerVars($server2)); 
        $result = false; 
        if ($response3->status() == '201') {
            $result = true;
        }  
        $this->assertEquals(true, $result);    
        $this->notSeeInDatabase('friends', ['user_id' => 1, 'friend_id' => 2]); 
        $this->notSeeInDatabase('friends', ['user_id' => 2, 'friend_id' => 1]); 
    }
    // the correct response of the getAllRequest.
    public function testGetAllRequest() { 
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
        $parameter2 = array(
            'email' => 'letsfae3@126.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp3',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter2, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $parameter = array(
            'email' => 'letsfae1@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp1',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters2 = array(
            'requested_user_id' => 3,
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/friends/request', $parameters2, [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication(); 
        $parameter2 = array(
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response2 = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter2, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response2->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameters3 = array(
            'requested_user_id' => 3,
        ); 
        $response2 = $this->call('post', 'http://'.$this->domain.'/friends/request', $parameters3, [], [], $this->transformHeadersToServerVars($server2));  
        $this->refreshApplication(); 
        $parameter3 = array(
            'email' => 'letsfae3@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp3',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response3 = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter3, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response3->getContent());
        $server3 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response3 = $this->call('get', 'http://'.$this->domain.'/friends/request', [], [], [], $this->transformHeadersToServerVars($server3));  
        $array3 = json_decode($response3->getContent());
        print_r($array3);
        for ($i = 1; $i < 3; $i++) {
            $this->seeJson([   
                        'friend_request_id' => $i, 
                        'request_user_id' => $i,
                        'request_user_name' => 'faeapp'.$i,
                        'request_email' => 'letsfae'.$i.'@126.com',   
                        'created_at' => $array3[$i - 1]->created_at,
            ]); 
        }
        $result = false; 
        if ($response3->status() == '200') {
            $result = true;
        }  
        $this->assertEquals(true, $result);     
    }
}
