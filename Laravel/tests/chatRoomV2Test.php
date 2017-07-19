 <?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing;
use App\ChatRooms;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\Geometry;
use App\Users;
use App\ChatRoomUsers;
use App\Sessions;
use App\Tags;


class ChatRoomV2Test extends TestCase {
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

    // the correct response of the create chatRoom.
    public function testCreated() { 
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
            'title' => 'This is the test.',
            'geo_latitude' => 34.2799, 
            'geo_longitude' => -118.2799,
            'duration' => 1440,
            'interaction_radius' => 100
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'chat_room_id' => 1,
        ]);
        $result = false; 
        if ($response->status() == '201') {
            $result = true;
        }  
        $this->assertEquals(true, $result);  
        $this->seeInDatabase('chat_rooms', ['user_id' => 1, 'title' => 'This is the test.', 'geolocation' => '0101000020E6100000A089B0E1E9915DC0401361C3D3234140', 'duration' => 1440, 'interaction_radius' => 100]);
        $this->seeInDatabase('chat_room_users', ['chat_room_id' => 1, 'user_id' => 1, 'unread_count' => 0]);
        $this->seeInDatabase('pin_helper', ['type' => 'chat_room', 'pin_id' => 1, 'geolocation' => '0101000020E6100000A089B0E1E9915DC0401361C3D3234140', 'duration' => 1440]);
    }

    // to test whether the input format is right.
    public function testCreated2() {
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
            'title' => 'This is the test.',
            'geo_latitude' => -118.99, //the wrong format of the latitude
            'geo_longitude' => -118.2799,
        ); 
        //create the comment.
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent());  
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Could not create chat room.' && $array2->errors->geo_latitude[0] == 'The geo latitude must be between -90 and 90.') {
            $result = true;
        }
        $this->assertEquals(true, $result);   
    }
    // test the response when the tags not found.
    public function testCreated3() { 
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
            'tag_ids' => '1;2',
            'title' => 'This is the test.',
            'geo_latitude' => 34.2799, 
            'geo_longitude' => -118.2799,
            'duration' => 1440,
            'interaction_radius' => 100
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
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
    
    //test the correct response of method of updateChatRoom.
    public function testUpdate() { 
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
        //create the comment.
        $parameters1 = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1380,
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters1, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent());
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test2.',
            'geo_latitude' => 35.5799,
            'geo_longitude' => -120.2799,
            'duration' => 1440,
            'interaction_radius' => 100
        ); 
        $response2 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2/1', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $array3 = json_decode($response2->getContent()); 
        $result = false;
        if ($response2->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
        $this->seeInDatabase('chat_rooms', ['title' => 'This is the test2.', 'geolocation' => '0101000020E6100000A089B0E1E9115EC0A779C7293ACA4140', 'duration' => 1440, 'interaction_radius' => 100]); 
        $this->seeInDatabase('pin_helper', ['geolocation' => '0101000020E6100000A089B0E1E9115EC0A779C7293ACA4140', 'duration' => 1440]);
    }

    //test whether the format of the given chat_room_id is valid.
    public function testUpdate2() { 
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
        //create the comment.
        $parameters1 = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1380,
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters1, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent());
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test2.',
        );  
        //wrong format of the chat_room_id.
        $response2 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2/fae', $parameters2, [], [], $this->transformHeadersToServerVars($server2));  
        $this->seeJson([
                    'message' => 'chat_room_id is not integer',
                    'error_code' => '400-3',
                    'status_code' => '400', 
        ]); 
        $result = false;
        if ($response2->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test whether the format of the input is valid. 
    public function testUpdate3() { 
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
        //create the comment.
        $parameters1 = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1380,
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters1, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent());
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test2.',
            'geo_latitude' => 34.2899,
            'geo_longitude' => -218.2799
        );   
        $response2 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2/1', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $array3 = json_decode($response2->getContent()); 
        $result = false; 
        if ($response2->status() == '422' && $array3->message == 'Could not update chat room.' && $array3->errors->geo_longitude[0] == 'The geo longitude must be between -180 and 180.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the chatroom with the given chat_room_id does not exist.
    public function testUpdate4() { 
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
        //create the comment.
        $parameters1 = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1380,
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters1, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response1->getContent());
        $this->refreshApplication();  
        $parameters2 = array(
            'title' => 'This is the test2.', 
            'geo_latitude' => 37.2899,
            'geo_longitude' => -118.2799
        );  
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2/2', $parameters2, [], [], $this->transformHeadersToServerVars($server2));  
        $this->seeJson([
                    'message' => 'chat room not found',
                    'error_code' => '404-5',
                    'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        } 
    }

    //test whether the user_id of the chat_room is the same as the current user_id.
    public function testUpdate5() { 
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
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        ); 
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication(); 
        $array = json_decode($login_response->getContent());  
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $parameter3 = array(
            'title' => 'This is the test1.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1380,
        );  
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameter3, [], [], $this->transformHeadersToServerVars($server1)); 
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
        $parameters1 = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1380,
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters1, [], [], $this->transformHeadersToServerVars($server2));  
        $array2 = json_decode($response1->getContent());
        $this->refreshApplication();  
        $parameters2 = array(
            'title' => 'This is the test2.', 
            'geo_latitude' => 37.2899,
            'geo_longitude' => -118.2799
        );  
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2/1', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                    'message' => 'You can not update this chat room',
                    'error_code' => '403-2',
                    'status_code' => '403', 
        ]); 
        $result = false;
        if ($response->status() == '403') {
            $result = true;
        } 
    }   
    //test the response when the tag not found.
    public function testUpdate6() { 
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
        //create the comment.
        $parameters1 = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1380,
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters1, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent());
        $this->refreshApplication(); 
        $parameters2 = array(
            'tag_ids' => '1;2',
            'title' => 'This is the test2.',
            'geo_latitude' => 35.5799,
            'geo_longitude' => -120.2799,
            'duration' => 1440,
            'interaction_radius' => 100
        ); 
        $response2 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2/1', $parameters2, [], [], $this->transformHeadersToServerVars($server2));  
        $this->seeJson([
                    'message' => 'tag not found',
                    'error_code' => '404-4',
                    'status_code' => '404', 
        ]); 
        $result = false;
        if ($response2->status() == '404') {
            $result = true;
        } 
    }
    // the correct response of the get chatRoom with the chat_room_id.
    public function testGetOne() {
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
        //create the chatRoom.
        $parameters1 = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'user_id' => 1,
            'duration' => 1440,
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters1, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent()); 
        $this->refreshApplication(); 
        //get the chatRoom
        $response2 = $this->call('get', 'http://'.$this->domain.'/chat_rooms_v2/'.$array2->chat_room_id, [], [], [], $this->transformHeadersToServerVars($server2)); 
        $array3 = json_decode($response2->getContent()); 
        $this->seeJson([
                'chat_room_id' => 1,
                'title' => 'This is the test.',
                'user_id' => 1, 
                'nick_name' => null,
                'geolocation' => array(
                    'latitude' => 34.2799,
                    'longitude' => -118.2799,
                ),
                'last_message' => null,
                'last_message_sender_id' => null,
                'last_message_type' => null,
                'last_message_timestamp' => null,  
                'created_at' => $array3->created_at,
                'capacity' => 50,
                'tag_ids' => null,
                'description' => null,
                'members' => array(1)
        ]);
        $result = false;
        if ($response2->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test whether the format of the chat_room_id is valid.
    public function testGetOne2() {
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
        //create the chatRoom.
        $parameters1 = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1440,
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters1, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent());
        $this->refreshApplication(); 
        //the format of the chat_room_id is not valid.
        //get the chat_room.
        $response = $this->call('get', 'http://'.$this->domain.'/chat_rooms_v2/letsfae', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                    'message' => 'chat_room_id is not integer',
                    'error_code' => '400-3',
                    'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        } 
    }

    // the chat_room with the given chat_room_id does not exist.
    public function testGetOne3() {
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
        //create the chatRoom.
        $parameters1 = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1440,
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters1, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent());
        $this->refreshApplication();  
        //test the chat_room with the chat_room -1 does not exist!
        //get the chat_room
        $response2 = $this->call('get', 'http://'.$this->domain.'/chat_rooms_v2/-1'.$array2->chat_room_id, [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                    'message' => 'chat room not found',
                    'error_code' => '404-5',
                    'status_code' => '404', 
        ]); 
        $result = false;
        if ($response2->status() == '404') {
            $result = true;
        }   
    }

    // the correct response of the method of getting all chatRomms of the given user.
    public function testGetFromUser() { 
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
        $parameters = array();
        $response = array();
        for ($i = 0; $i < 31; $i++) {
            $parameters[$i] = array(
                'title' => 'This is the test'.$i,
                'geo_longitude' => -118.2799,
                'geo_latitude' => 34.2799, 
                'duration' => 1440,
            );
        }
        //create the chatRooms.
        for ($i = 0; $i < 31; $i++) {
            $response[$i] = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters[$i], [], [], $this->transformHeadersToServerVars($server2)); 
            // sleep(1);
            $this->refreshApplication();
        } 
        $content = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        ); 
        //get the chatRoom of the user with the user_id.
        //get the chatRoom of the page 1.
        $response_page1 = $this->call('get', 'http://'.$this->domain.'/chat_rooms_v2/users/'.$array->user_id, $content, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response_page1->getContent());  
        for ($i = 0; $i < 30; $i++) {
            $this->seeJson([  
                        'chat_room_id' => $array2[$i]->chat_room_id,
                        'title' => $array2[$i]->title,
                        'user_id' => $array2[$i]->user_id, 
                        'geolocation' => array(
                        'latitude' => $array2[$i]->geolocation->latitude,
                        'longitude' => $array2[$i]->geolocation->longitude,
                        ),
                        'last_message' => $array2[$i]->last_message,
                        'last_message_sender_id' => $array2[$i]->last_message_sender_id,
                        'last_message_type' => $array2[$i]->last_message_type,
                        'last_message_timestamp' => $array2[$i]->last_message_timestamp,  
                        'created_at' => $array2[$i]->created_at,
                        'capacity' => 50,
                        'tag_ids' => $array2[$i]->tag_ids,
                        'description' =>  $array2[$i]->description,
                        'members' => array(1),
            ]);         
        }
        $this->refreshApplication();
        $content2 = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 2,
        );
        // //get the comments of the page 2.
        $response_page2 = $this->call('get', 'http://'.$this->domain.'/chat_rooms_v2/users/'.$array->user_id, $content2, [], [], $this->transformHeadersToServerVars($server2));
        $array3 = json_decode($response_page2->getContent());
        $this->seeJson([ 
                        'chat_room_id' => $array3[0]->chat_room_id,
                        'title' => $array3[0]->title,
                        'user_id' => $array3[0]->user_id, 
                        'geolocation' => array(
                        'latitude' => $array3[0]->geolocation->latitude,
                        'longitude' => $array3[0]->geolocation->longitude,
                        ),
                        'last_message' => $array3[0]->last_message,
                        'last_message_sender_id' => $array3[0]->last_message_sender_id,
                        'last_message_type' => $array3[0]->last_message_type,
                        'last_message_timestamp' => $array3[0]->last_message_timestamp,  
                        'created_at' => $array3[0]->created_at,
                        'capacity' => 50,
                        'tag_ids' => $array3[0]->tag_ids,
                        'description' =>  $array3[0]->description,
                        'members' => array(1),
        ]); 
        $result = false;
        if ($response_page1->headers->get('page') == '1' && $response_page1->headers->get('total-pages') == '2' && $response_page1->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test whether the user with user_id exists.
    public function testGetFromUser2() { 
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
        //the user does not exist.
        //get the chatRoom.
        $response = $this->call('get', 'http://'.$this->domain.'/chat_rooms_v2/users/2', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                    'message' => 'user not found',
                    'error_code' => '404-5',
                    'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }    
    }

    //test whether the format of the user_id is right.
    public function testGetFromUser3() { 
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
        //the format of the user_id is not valid and the user does not exist.
        //get the chatroom.
        $response = $this->call('get', 'http://'.$this->domain.'/chat_rooms_v2/users/letfae', [], [], [], $this->transformHeadersToServerVars($server2));  
        $this->seeJson([
                    'message' => 'user_id is not integer',
                    'error_code' => '400-3',
                    'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }    
    }

    //test whether the format of the input is valid.
    public function testGetFromUser4() { 
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
        //get the chat_room.
        //the input of the start_time is not valid.
        $content = array(
            'start_time' => '2016-06-08 21:22:3',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 1,
        );
        $response = $this->call('get', 'http://'.$this->domain.'/chat_rooms_v2/users/'.$array->user_id, $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent());  
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Could not get user chatrooms.' && $array2->errors->start_time[0] == 'The start time does not match the format Y-m-d H:i:s.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    } 

    // test the select page is larger than the total page.
    public function testGetFromUser5() {  
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
        //create the chatRoom.
        $parameters2 = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1380,
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        //get the chatRoom.
        $this->refreshApplication();
        $content = array(
            'start_time' => '2016-06-08 21:22:39',
            'end_time' => date("Y-m-d H:i:s"),
            'page' => 2,//the page 2 does not exist!
        );
        $response = $this->call('get', 'http://'.$this->domain.'/chat_rooms_v2/users/'.$array->user_id, $content, [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response->getContent());
        $result = false;
        $this->seeJson([]);
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // the correct response of the method of sending message to chatRoom.
    public function testSend() { 
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
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1380,
            'interaction_radius' => 1
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        //get the chatRoom.
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
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $session = Sessions::find(2); 
        //big difference of the geolocation radius;
        $session->location = new Point(34.2799, -118.2799);
        $session->save();
        $server3 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        // send the message
        $parameters3 = array(
            'message' => 'send message',
            'type' => 'text',  
        ); 
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2/1/message', $parameters3, [], [], $this->transformHeadersToServerVars($server3)); 
        $result = false;
        if ($response1->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
        $this->seeInDatabase('chat_rooms', ['last_message_sender_id' => 2, 'last_message' => 'send message', 'last_message_type' => 'text']);
        $this->seeInDatabase('chat_room_users', ['chat_room_id' => 1, 'user_id' => 1, 'unread_count' => 1]);
        $this->seeInDatabase('chat_room_users', ['chat_room_id' => 1, 'user_id' => 2, 'unread_count' => 0]);
    }

    // test the response when the format of the chat_room_id is not integer.
    public function testSend2() { 
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
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1380,
            'interaction_radius' => 1
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        //get the chatRoom.
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
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $session = Sessions::find(2); 
        //big difference of the geolocation radius;
        $session->location = new Point(34.2799, -118.2799);
        $session->save();
        $server3 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        // send the message
        $parameters3 = array(
            'message' => 'send message',
            'type' => 'text',  
        ); 
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2/wrong_format/message', $parameters3, [], [], $this->transformHeadersToServerVars($server3));  
        $this->seeJson([
                    'message' => 'chat_room_id is not integer',
                    'error_code' => '400-3',
                    'status_code' => '400', 
        ]); 
        $result = false;
        if ($response1->status() == '400') {
            $result = true;
        }    
    }

     // test the response when the input format is wrong.
    public function testSend3() { 
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
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1380,
            'interaction_radius' => 1
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        //get the chatRoom.
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
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $session = Sessions::find(2); 
        //big difference of the geolocation radius;
        $session->location = new Point(34.2799, -118.2799);
        $session->save();
        $server3 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        // send the message
        $parameters3 = array(
            'message' => 'send message',
            'type' => 'video',  
        ); 
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2/1/message', $parameters3, [], [], $this->transformHeadersToServerVars($server3)); 
        $array2 = json_decode($response1->getContent());
        $result = false;
        if ($response1->status() == '422' && $array2->message == 'Could not send message.' && $array2->errors->type[0] == 'The selected type is invalid.') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    } 

    // test the response when the chatRoom information does not exist with the given chat_room_id.
    public function testSend4() { 
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
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1380,
            'interaction_radius' => 1
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        //get the chatRoom.
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
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $session = Sessions::find(2); 
        //big difference of the geolocation radius;
        $session->location = new Point(34.2799, -118.2799);
        $session->save();
        $server3 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        // send the message
        $parameters3 = array(
            'message' => 'send message',
            'type' => 'text',  
        ); 
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2/2/message', $parameters3, [], [], $this->transformHeadersToServerVars($server3));  
        $this->seeJson([
                    'message' => 'chat room not found',
                    'error_code' => '404-5',
                    'status_code' => '404', 
        ]); 
        $result = false;
        if ($response1->status() == '404') {
            $result = true;
        }    
    }   

    // test the response when the unread_count of the user with self_user_id is bigger than 0;
    public function testSend5() { 
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
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1380,
            'interaction_radius' => 1
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        //get the chatRoom.
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
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $session = Sessions::find(2); 
        //big difference of the geolocation radius;
        $session->location = new Point(34.2799, -118.2799);
        $session->save();
        $server3 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $chatRoomUsers = ChatRoomUsers::create([
                    'chat_room_id' => 1,
                    'user_id' => 2,
                    'unread_count' => 1
        ]);   
        // send the message
        $parameters3 = array(
            'message' => 'send message',
            'type' => 'text',  
        ); 
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2/1/message', $parameters3, [], [], $this->transformHeadersToServerVars($server3));  
        $this->seeJson([
                    'message' => 'Please mark unread messages before sending new messages!',
                    'error_code' => '400-1',
                    'status_code' => '400', 
        ]); 
        $result = false;
        if ($response1->status() == '400') {
            $result = true;
        }    
    }   
 
    // test the response when the capacity of the chat_room exceed the limit.
    public function testSend6() { 
        $this->markTestSkipped(); 
        //register of the user.
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        for ($i = 1; $i < 7; $i++) { 
            ${'parameters' . $i}  = array(
            'email' => 'letsfae'.$i.'@126.com', 
            'password' => 'letsfaego',
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp'.$i,
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
            );
            $response = $this->call('post', 'http://'.$this->domain.'/users', ${'parameters' . $i}, [], [], $this->transformHeadersToServerVars($server));
            $this->refreshApplication();
        } 
        $parameters = array(
            'email' => 'letsfae1@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp1',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $array = json_decode($login_response->getContent());
        $parameter = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1380,
            'interaction_radius' => 1,
            'capacity' => 5
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameter, [], [], $this->transformHeadersToServerVars($server2));  
        $this->refreshApplication();
        for ($i = 2; $i < 6; $i++) { 
            $chatRoomUsers = ChatRoomUsers::create([
                        'chat_room_id' => 1,
                        'user_id' => $i,
                        'unread_count' => 0
            ]); 
        } 
        $parameters = array(
            'email' => 'letsfae6@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $session = Sessions::find(6); 
        //big difference of the geolocation radius;
        $session->location = new Point(34.2799, -118.2799);
        $session->save();
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        // send the message
        $parameters3 = array(
            'message' => 'send message',
            'type' => 'text',  
        ); 
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2/1/message', $parameters3, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                    'message' => 'this chat room has been filled to capacity',
                    'error_code' => '400-4',
                    'status_code' => '400', 
        ]); 
        $result = false;
        if ($response1->status() == '400') {
            $result = true;
        } 
    } 

    // test the response when the location not found.
    public function testSend7() { 
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
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1380,
            'interaction_radius' => 1
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        //get the chatRoom.
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
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent()); 
        $server3 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        // send the message
        $parameters3 = array(
            'message' => 'send message',
            'type' => 'text',  
        ); 
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2/1/message', $parameters3, [], [], $this->transformHeadersToServerVars($server3)); 
        $this->seeJson([
                    'message' => 'location not found',
                    'error_code' => '404-7',
                    'status_code' => '404', 
        ]); 
        $result = false;
        if ($response1->status() == '404') {
            $result = true;
        }  
    }
    // test the response when the current client is not mobile.
    public function testSend8() { 
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
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1380,
            'interaction_radius' => 1
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        //get the chatRoom.
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
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent()); 
        $server3 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $session = Sessions::find(2); 
        //big difference of the geolocation radius;
        $session->is_mobile = false;
        $session->save();
        // send the message
        $parameters3 = array(
            'message' => 'send message',
            'type' => 'text',  
        ); 
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2/1/message', $parameters3, [], [], $this->transformHeadersToServerVars($server3)); 
        $this->seeJson([
                    'message' => 'current client is not mobile',
                    'error_code' => '400-5',
                    'status_code' => '400', 
        ]); 
        $result = false;
        if ($response1->status() == '400') {
            $result = true;
        }  
    }

    // test the response when it is too far away.
    public function testSend9() { 
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
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test.',
            'geo_longitude' => -118.2799,
            'geo_latitude' => 34.2799, 
            'duration' => 1380,
            'interaction_radius' => 1
        ); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );
        $response2 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        //get the chatRoom.
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
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent()); 
        $server3 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        $session = Sessions::find(2); 
        //big difference of the geolocation radius;
        $session->location = new Point(24.2799, -128.2799);
        $session->save();
        // send the message
        $parameters3 = array(
            'message' => 'send message',
            'type' => 'text',  
        ); 
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms_v2/1/message', $parameters3, [], [], $this->transformHeadersToServerVars($server3)); 
        $this->seeJson([
                    'message' => 'too far away',
                    'error_code' => '403-3',
                    'status_code' => '403', 
        ]); 
        $result = false;
        if ($response1->status() == '403') {
            $result = true;
        }  
    }
    // the correct response of the method of getUnread.
    public function testGetUnread() { 
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
        $chatRoom = ChatRooms::create([
                    'user_id' => 1,
                    'title' => 'The chatRoom one',
                    'geolocation' => new Point(34.2799, -118.2799), 
                    'duration' => 1440
        ]);
        $chatRoomUsers = ChatRoomUsers::create([
                    'chat_room_id' => 1,
                    'user_id' => 1,
                    'unread_count' => 0
        ]);  
        $chatRoomUsers = ChatRoomUsers::create([
                    'chat_room_id' => 1,
                    'user_id' => 2,
                    'unread_count' => 0
        ]);  
        $parameter3 = array(
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter3, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameters = array(
            'email' => 'letsfae3@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp3',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $session1 = Sessions::where('user_id', '=', 3)->first();
        $session1->location = new Point(34.2799,-118.2799);
        $session1->save(); 
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        // send the message
        $parameters3 = array(
            'message' => 'send message',
            'type' => 'text',  
        ); 
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms/1/message', $parameters3, [], [], $this->transformHeadersToServerVars($server2)); 
        $chatRoomUsers = ChatRoomUsers::where('user_id', 3)->first();
        $chatRoomUsers->unread_count = 1;
        $chatRoomUsers->save();
        $this->refreshApplication();  
        //get unread.
        $response2 = $this->call('get', 'http://'.$this->domain.'/chat_rooms/message/unread', [], [], [], $this->transformHeadersToServerVars($server2));  
        $array2 = json_decode($response2->getContent());  
        $this->seeJson([  
                    'chat_room_id' => 1,
                    'title' => 'The chatRoom one',
                    'user_id' => 1, 
                    'geolocation' => array(
                    'latitude' => 34.2799,
                    'longitude' => -118.2799,
                    ),
                    'last_message' => 'send message',
                    'last_message_sender_id' => 3,
                    'last_message_sender_name' => 'faeapp3',
                    'last_message_type' => 'text',
                    'last_message_timestamp' => $array2[0]->last_message_timestamp, 
                    'unread_count' => 1,
                    'created_at' => $array2[0]->created_at,
                    'server_sent_timestamp' => $array2[0]->server_sent_timestamp,
                    'capacity' => 50,
                    'tag_ids' => null,
                    'description' => null,
        ]);    
        $result = false; 
        if ($response2->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // the correct response of the method of markRead.
    public function testMarkRead() { 
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
        $chatRoom = ChatRooms::create([
                    'user_id' => 1,
                    'title' => 'The chatRoom one',
                    'geolocation' => new Point(34.2799, -118.2799), 
                    'duration' => 1440
        ]);
        $chatRoomUsers = ChatRoomUsers::create([
                    'chat_room_id' => 1,
                    'user_id' => 1,
                    'unread_count' => 0
        ]);  
        $chatRoomUsers = ChatRoomUsers::create([
                    'chat_room_id' => 1,
                    'user_id' => 2,
                    'unread_count' => 0
        ]); 
        $parameter3= array(
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter3, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameters = array(
            'email' => 'letsfae3@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp3',
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
        // send the message
        $parameters3 = array(
            'message' => 'send message',
            'type' => 'text',  
        ); 
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms/1/message', $parameters3, [], [], $this->transformHeadersToServerVars($server2)); 
        $chatRoomUsers = ChatRoomUsers::where('user_id', 3)->first();
        $chatRoomUsers->unread_count = 1;
        $chatRoomUsers->save();
        $this->refreshApplication();  
        //get unread.
        $response2 = $this->call('post', 'http://'.$this->domain.'/chat_rooms/1/message/read', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response2->getContent());    
        $result = false; 
        if ($response2->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the response when the format of the chat_room_id is wrong.
    public function testMarkRead2() { 
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
        $chatRoom = ChatRooms::create([
                    'user_id' => 1,
                    'title' => 'The chatRoom one',
                    'geolocation' => new Point(34.2799, -118.2799), 
                    'duration' => 1440
        ]);
        $chatRoomUsers = ChatRoomUsers::create([
                    'chat_room_id' => 1,
                    'user_id' => 1,
                    'unread_count' => 0
        ]);  
        $chatRoomUsers = ChatRoomUsers::create([
                    'chat_room_id' => 1,
                    'user_id' => 2,
                    'unread_count' => 0
        ]);  
        $parameter3 = array(
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter3, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameters = array(
            'email' => 'letsfae3@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp3',
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
        // send the message
        $parameters3 = array(
            'message' => 'send message',
            'type' => 'text',  
        ); 
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms/1/message', $parameters3, [], [], $this->transformHeadersToServerVars($server2)); 
        $chatRoomUsers = ChatRoomUsers::where('user_id', 3)->first();
        $chatRoomUsers->unread_count = 1;
        $chatRoomUsers->save();
        $this->refreshApplication();  
        //get unread.
        //wrong format of the chat_room_id.
        $response2 = $this->call('post', 'http://'.$this->domain.'/chat_rooms/wrong_format/message/read', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response2->getContent());   
        $result = false;
        if ($response2->status() == '400' && $array2->message == 'Bad Request') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
    }

    // test the response when the chatRoom information does not exist with the given chat_room_id. 
    public function testMarkRead3() { 
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
        $chatRoom = ChatRooms::create([
                    'user_id' => 1,
                    'title' => 'The chatRoom one',
                    'geolocation' => new Point(34.2799, -118.2799), 
                    'duration' => 1440
        ]);
        $chatRoomUsers = ChatRoomUsers::create([
                    'chat_room_id' => 1,
                    'user_id' => 1,
                    'unread_count' => 0
        ]);  
        $chatRoomUsers = ChatRoomUsers::create([
                    'chat_room_id' => 1,
                    'user_id' => 2,
                    'unread_count' => 0
        ]);  
        $parameter3 = array(
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter3, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameters = array(
            'email' => 'letsfae3@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp3',
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
        // send the message
        $parameters3 = array(
            'message' => 'send message',
            'type' => 'text',  
        ); 
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms/1/message', $parameters3, [], [], $this->transformHeadersToServerVars($server2)); 
        $chatRoomUsers = ChatRoomUsers::where('user_id', 3)->first();
        $chatRoomUsers->unread_count = 1;
        $chatRoomUsers->save();
        $this->refreshApplication();  
        //get unread.
        //wrong format of the chat_room_id.
        $response2 = $this->call('post', 'http://'.$this->domain.'/chat_rooms/2/message/read', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $array2 = json_decode($response2->getContent());   
        $result = false;
        if ($response2->status() == '404' && $array2->message == 'Not Found') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    // the correct response of the method of getHistory of the chatRoom that the user participates in.
    public function testGetHistory() { 
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
        $chatRoom = ChatRooms::create([
                    'user_id' => 1,
                    'title' => 'This is the test.',
                    'geolocation' => new Point(34.2799, -118.2799), 
                    'duration' => 1440
        ]);
        $chatRoomUsers = ChatRoomUsers::create([
                    'chat_room_id' => 1,
                    'user_id' => 1,
                    'unread_count' => 0
        ]); 
        $chatRoom = ChatRooms::create([
                    'user_id' => 1,
                    'title' => 'This is the test2.',
                    'geolocation' => new Point(34.2799, -118.2799), 
                    'duration' => 1440
        ]);
        $chatRoomUsers = ChatRoomUsers::create([
                    'chat_room_id' => 2,
                    'user_id' => 1,
                    'unread_count' => 0
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
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        // send the message
        $parameters3 = array(
            'message' => 'send message to chatRoom 1',
            'type' => 'text',  
        );
        $parameters4 = array(
            'message' => 'send message to chatRoom 2',
            'type' => 'text',  
        );  
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms/1/message', $parameters3, [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication();  
        $response2 = $this->call('post', 'http://'.$this->domain.'/chat_rooms/2/message', $parameters4, [], [], $this->transformHeadersToServerVars($server2));  
        $this->refreshApplication();  
        $response3 = $this->call('get', 'http://'.$this->domain.'/chat_rooms', [], [], [], $this->transformHeadersToServerVars($server2));  
        $array2 = json_decode($response3->getContent());   
        for ($i = 0; $i < 2; $i++) {
            $this->seeJson([  
                        'chat_room_id' => $array2[$i]->chat_room_id,
                        'title' => $array2[$i]->title,
                        'user_id' => $array2[$i]->user_id, 
                        'geolocation' => array(
                        'latitude' => $array2[$i]->geolocation->latitude,
                        'longitude' => $array2[$i]->geolocation->longitude,
                        ),
                        'last_message' => $array2[$i]->last_message,
                        'last_message_sender_id' => $array2[$i]->last_message_sender_id,
                        'last_message_sender_name' => $array2[$i]->last_message_sender_name,
                        'last_message_type' => $array2[$i]->last_message_type,
                        'last_message_timestamp' => $array2[$i]->last_message_timestamp,  
                        'unread_count' => $array2[$i]->unread_count,
                        'created_at' => $array2[$i]->created_at,
                        'server_sent_timestamp' => $array2[$i]->server_sent_timestamp,
                        'capacity' => 50,
                        'tag_ids' => null,
                        'description' => null
            ]);    
        }   
        $result = false;
        if ($response3->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
    }

    // the correct response of the method of getUserList of the chatRoom.
    public function testGetUserList() { 
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
        $chatRoom = ChatRooms::create([
                    'user_id' => 1,
                    'title' => 'This is the test.',
                    'geolocation' => new Point(34.2799, -118.2799), 
                    'duration' => 1440
        ]);
        $chatRoomUsers = ChatRoomUsers::create([
                    'chat_room_id' => 1,
                    'user_id' => 1,
                    'unread_count' => 0
        ]);  
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
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
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
        // send the message
        $parameters3 = array(
            'message' => 'send message to chatRoom 1',
            'type' => 'text',  
        );  
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms/1/message', $parameters3, [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication();  
        $response2 = $this->call('get', 'http://'.$this->domain.'/chat_rooms/1/users', [], [], [], $this->transformHeadersToServerVars($server2));  
        $array2 = json_decode($response2->getContent());  
        for ($i = 0; $i < 2; $i++) {
            $this->seeJson([  
                        'chat_room_id' => $array2[$i]->chat_room_id,
                        'user_id' => $array2[$i]->user_id,  
                        'created_at' => $array2[$i]->created_at,
            ]);    
        }   
        $result = false;
        if ($response2->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
    }

    // the response when the chat_room_id format is not valid.
    public function testGetUserList2() { 
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
        $chatRoom = ChatRooms::create([
                    'user_id' => 1,
                    'title' => 'This is the test.',
                    'geolocation' => new Point(34.2799, -118.2799), 
                    'duration' => 1440
        ]);
        $chatRoomUsers = ChatRoomUsers::create([
                    'chat_room_id' => 1,
                    'user_id' => 1,
                    'unread_count' => 0
        ]);  
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
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
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
        // send the message
        $parameters3 = array(
            'message' => 'send message to chatRoom 1',
            'type' => 'text',  
        );  
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms/1/message', $parameters3, [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication();  
        // wrong format of the chat_room_id.
        $response2 = $this->call('get', 'http://'.$this->domain.'/chat_rooms/wrong_format/users', [], [], [], $this->transformHeadersToServerVars($server2));  
        $array2 = json_decode($response2->getContent());   
        $result = false;
        if ($response2->status() == '400' && $array2->message == 'Bad Request') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
    }

    // the the response when the chatRomm information does not exist with the given chat_room_id.
    public function testGetUserList3() { 
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
        $chatRoom = ChatRooms::create([
                    'user_id' => 1,
                    'title' => 'This is the test.',
                    'geolocation' => new Point(34.2799, -118.2799), 
                    'duration' => 1440
        ]);
        $chatRoomUsers = ChatRoomUsers::create([
                    'chat_room_id' => 1,
                    'user_id' => 1,
                    'unread_count' => 0
        ]);  
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
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
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
        // send the message
        $parameters3 = array(
            'message' => 'send message to chatRoom 1',
            'type' => 'text',  
        );  
        $response1 = $this->call('post', 'http://'.$this->domain.'/chat_rooms/1/message', $parameters3, [], [], $this->transformHeadersToServerVars($server2));
        $this->refreshApplication();  
        // the chat_room_id does not exist.
        $response2 = $this->call('get', 'http://'.$this->domain.'/chat_rooms/2/users', [], [], [], $this->transformHeadersToServerVars($server2));  
        $array2 = json_decode($response2->getContent());   
        $result = false;
        if ($response2->status() == '404' && $array2->message == 'Not Found') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }
}
