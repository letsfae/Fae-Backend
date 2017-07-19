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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


class chatRoomFileTest extends TestCase
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

    // the correct response of the setChatRoomCoverImage.
    public function testSetChatRoomCoverImage() { 
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
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test.',
            'geo_latitude' => 34.2799, 
            'geo_longitude' => -118.2799,
            'duration' => 1440,
            'interaction_radius' => 100
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();  
        $parameters3 = array( 
            'chat_room_id' => 1, 
        );  
        $path = storage_path('app/files/'.'cover_image.jpg');
        $response = $this->call('post', 'http://'.$this->domain.'/files/chat_rooms/cover_image', $parameters3, [], ['cover_image' => new UploadedFile(storage_path('app/files/'.'cover_image.jpg'), 'cover_image.jpg', filesize($path), 'image/jpg', null, true)], $this->transformHeadersToServerVars($server2));
        $this->assertFileExists($path);
    }
    // test the response when the file does not exist.
    public function testSetChatRoomCoverImage2() { 
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
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test.',
            'geo_latitude' => 34.2799, 
            'geo_longitude' => -118.2799,
            'duration' => 1440,
            'interaction_radius' => 100
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();
        $parameters3 = array( 
            'chat_room_id' => 1, 
        );  
        $path = storage_path('app/files/'.'cover_image.jpg');
        $response = $this->call('post', 'http://'.$this->domain.'/files/chat_rooms/cover_image', $parameters3, [], ['cover_image' => new UploadedFile($path, 'cover_image.jpg')], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'Bad Request',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }
    // test the response when the chat_room _id is not valid.
    public function testSetChatRoomCoverImage3() { 
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
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test.',
            'geo_latitude' => 34.2799, 
            'geo_longitude' => -118.2799,
            'duration' => 1440,
            'interaction_radius' => 100
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();  
        $parameters3 = array( 
            'chat_room_id' => 'fae', 
        );  
        $path = storage_path('app/files/'.'cover_image.jpg');
        $response = $this->call('post', 'http://'.$this->domain.'/files/chat_rooms/cover_image', $parameters3, [], ['cover_image' => new UploadedFile(storage_path('app/files/'.'cover_image.jpg'), 'cover_image.jpg', filesize($path), 'image/jpg', null, true)], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'chat_room_id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }
    // test the response when the chat_room does not exist.
    public function testSetChatRoomCoverImage4() { 
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
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test.',
            'geo_latitude' => 34.2799, 
            'geo_longitude' => -118.2799,
            'duration' => 1440,
            'interaction_radius' => 100
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();  
        $parameters3 = array( 
            'chat_room_id' => 2, 
        );  
        $path = storage_path('app/files/'.'cover_image.jpg');
        $response = $this->call('post', 'http://'.$this->domain.'/files/chat_rooms/cover_image', $parameters3, [], ['cover_image' => new UploadedFile(storage_path('app/files/'.'cover_image.jpg'), 'cover_image.jpg', filesize($path), 'image/jpg', null, true)], $this->transformHeadersToServerVars($server2));
        $this->seeJson([
                 'message' => 'chat room not found',
                 'error_code' => '404-5',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }
    // test the response when the chat_room_user not found.
    public function testSetChatRoomCoverImage5() { 
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
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test.',
            'geo_latitude' => 34.2799, 
            'geo_longitude' => -118.2799,
            'duration' => 1440,
            'interaction_radius' => 100
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
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
        $parameters22 = array(
            'email' => 'letsfae2@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp2',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response2 = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters22, [], [], $this->transformHeadersToServerVars($server));
        $array2 = json_decode($login_response2->getContent());
        $server3 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array2->debug_base64ed,
        ); 
        $this->refreshApplication();  
        $parameters3 = array( 
            'chat_room_id' => 1, 
        );  
        $path = storage_path('app/files/'.'cover_image.jpg');
        $response = $this->call('post', 'http://'.$this->domain.'/files/chat_rooms/cover_image', $parameters3, [], ['cover_image' => new UploadedFile(storage_path('app/files/'.'cover_image.jpg'), 'cover_image.jpg', filesize($path), 'image/jpg', null, true)], $this->transformHeadersToServerVars($server3));
        $this->seeJson([
                 'message' => 'chat room user not found',
                 'error_code' => '404-8',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }
    // the correct response of the getChatRoomCoverImage.
    public function testGetChatRoomCoverImage() { 
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
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test.',
            'geo_latitude' => 34.2799, 
            'geo_longitude' => -118.2799,
            'duration' => 1440,
            'interaction_radius' => 100
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();  
        $parameters3 = array( 
            'chat_room_id' => 1, 
        );  
        $path = storage_path('app/files/'.'cover_image.jpg');
        $response = $this->call('post', 'http://'.$this->domain.'/files/chat_rooms/cover_image', $parameters3, [], ['cover_image' => new UploadedFile(storage_path('app/files/'.'cover_image.jpg'), 'cover_image.jpg', filesize($path), 'image/jpg', null, true)], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication(); 
        $response2 = $this->call('get', 'http://'.$this->domain.'/files/chat_rooms/1/cover_image', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $result = false;
        if ($response2->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }
    // test the response when the chat_room_id is not valid.
    public function testGetChatRoomCoverImage2() { 
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
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test.',
            'geo_latitude' => 34.2799, 
            'geo_longitude' => -118.2799,
            'duration' => 1440,
            'interaction_radius' => 100
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();  
        $parameters3 = array( 
            'chat_room_id' => 1, 
        );  
        $path = storage_path('app/files/'.'cover_image.jpg');
        $response = $this->call('post', 'http://'.$this->domain.'/files/chat_rooms/cover_image', $parameters3, [], ['cover_image' => new UploadedFile(storage_path('app/files/'.'cover_image.jpg'), 'cover_image.jpg', filesize($path), 'image/jpg', null, true)], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication(); 
        $response2 = $this->call('get', 'http://'.$this->domain.'/files/chat_rooms/wrong/cover_image', [], [], [], $this->transformHeadersToServerVars($server2)); 
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
    // test the response when the file does not exist.
    public function testGetChatRoomCoverImage3() { 
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
        $this->refreshApplication(); 
        $parameters2 = array(
            'title' => 'This is the test.',
            'geo_latitude' => 34.2799, 
            'geo_longitude' => -118.2799,
            'duration' => 1440,
            'interaction_radius' => 100
        ); 
        //create the chatRoom.
        $response = $this->call('post', 'http://'.$this->domain.'/chat_rooms', $parameters2, [], [], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication();  
        $parameters3 = array( 
            'chat_room_id' => 1, 
        );  
        $path = storage_path('app/files/'.'cover_image.jpg');
        $response = $this->call('post', 'http://'.$this->domain.'/files/chat_rooms/cover_image', $parameters3, [], ['cover_image' => new UploadedFile(storage_path('app/files/'.'cover_image.jpg'), 'cover_image.jpg', filesize($path), 'image/jpg', null, true)], $this->transformHeadersToServerVars($server2)); 
        $this->refreshApplication(); 
        $response2 = $this->call('get', 'http://'.$this->domain.'/files/chat_rooms/2/cover_image', [], [], [], $this->transformHeadersToServerVars($server2)); 
        $this->seeJson([
                 'message' => 'Not Found',
                 'error_code' => '404-3',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response2->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }
}
