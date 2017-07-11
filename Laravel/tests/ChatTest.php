<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Chats;

class ChatTest extends TestCase {
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
        $this->beforeApplicationDestroyed(function () {
            DB::disconnect();
        });
        parent::tearDown();
    }

    //test correct response of the method of sending message.
    public function testSend() { 
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
            'email' => 'letsfae@yahoo.com',
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
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        $parameters2 = array(
             'receiver_id' => 2,
             'message' => 'Hello world',
             'type' => 'text',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/chats', $parameters2, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent());  
        $this->seeJson([
                'chat_id' => $array2->chat_id,
        ]);
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
        $this->seeInDatabase('chats', ['user_a_id' => 1, 'user_b_id' => 2, 'user_b_unread_count' => 1]);
    }

    //test whether the input format is correct.
    public function testSend2() { 
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
            'email' => 'letsfae@yahoo.com',
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
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        //no such receiver_id in the user table.
        $parameters2 = array(
             'receiver_id' => 3,
             'message' => 'Hello world',
             'type' => 'text',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/chats', $parameters2, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent());  
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Could not send message.' && $array2->errors->receiver_id[0] == 'The selected receiver id is invalid.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test whether the unread messages has been marked.
    public function testSend3() {  
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
            'email' => 'letsfae@yahoo.com',
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
        $chat = Chats::create([
            'user_a_id' => 1,
            'user_b_id' => 2,
            'last_message_sender_id' => 1,
            'last_message' => 'Hello world',
            'last_message_type' => 'text',
            'user_a_unread_count' => 1,
            'user_b_unread_count' => 1,
            'last_message_timestamp' => '2016-07-16 22:19:17',
        ]);
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters2 = array(
             'receiver_id' => 2,
             'message' => 'Hello world2',
             'type' => 'text',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/chats', $parameters2, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent());  
        $this->seeJson([
                 'message' => 'Please mark unread messages before sending new messages!',
                 'error_code' => '400-1',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);

    }

    //test the response when the sender_id is the same as the receiver_id. 
    public function testSend4() {   
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
            'email' => 'letsfae@yahoo.com',
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
        $chat = Chats::create([
            'user_a_id' => 1,
            'user_b_id' => 2,
            'last_message_sender_id' => 1,
            'last_message' => 'Hello world',
            'last_message_type' => 'text',
            'user_a_unread_count' => 0,
            'user_b_unread_count' => 1,
            'last_message_timestamp' => '2016-07-16 22:19:17',
        ]);
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters2 = array(
             'receiver_id' => 1,
             'message' => 'Hello world2',
             'type' => 'text',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/chats', $parameters2, [], [], $this->transformHeadersToServerVars($server2));    

         $this->seeJson([
                 'message' => 'You can not send messages to yourself!',
                 'error_code' => '400-2',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test correct response of the method of getting unread. 
    public function testGetUnread() {  
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
            'email' => 'letsfae@yahoo.com',
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
        $chat = Chats::create([
            'user_a_id' => 1,
            'user_b_id' => 2,
            'last_message_sender_id' => 1,
            'last_message' => 'Hello world',
            'last_message_type' => 'text',
            'user_a_unread_count' => 1,
            'user_b_unread_count' => 1,
            'last_message_timestamp' => '2016-07-16 22:19:17',
        ]);
        $chat1 = Chats::create([
            'user_a_id' => 1,
            'user_b_id' => 2,
            'last_message_sender_id' => 1,
            'last_message' => 'Hello world2',
            'last_message_type' => 'text',
            'user_a_unread_count' => 1,
            'user_b_unread_count' => 1,
            'last_message_timestamp' => '2016-07-16 22:19:20',
        ]);
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $response = $this->call('get', 'http://'.$this->domain.'/chats/unread', [], [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        for ($i = 0; $i < 2; $i++) {
            $this->seeJson([
                    'chat_id' => $array2[0]->chat_id,
                    'last_message' => $array2[0]->last_message,
                    'last_message_sender_id' => $array2[0]->last_message_sender_id,
                    'last_message_sender_name' => $array2[0]->last_message_sender_name,
                    'last_message_timestamp' => $array2[0]->last_message_timestamp,
                    'last_message_type' => $array2[0]->last_message_type,
                    'unread_count' => $array2[0]->unread_count,
                    'server_sent_timestamp' => $array2[0]->server_sent_timestamp
            ]);
        }
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test response when the last message sender is not found. 
    // public function testGetUnread2() {  
    //     $this->markTestSkipped();
    //     $parameter1 = array(
    //         'email' => 'letsfae@126.com',
    //         'password' => 'letsfaego',
    //         'first_name' => 'kevin',
    //         'last_name' => 'zhang',
    //         'user_name' => 'faeapp',
    //         'gender' => 'male',
    //         'birthday' => '1992-02-02',
    //     );
    //     $server = array(
    //         'Accept' => 'application/x.faeapp.v1+json', 
    //         'Fae-Client-Version' => 'ios-0.0.1',
    //     );
    //     $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
    //     $this->refreshApplication();
    //     $parameter2 = array(
    //         'email' => 'letsfae@yahoo.com',
    //         'password' => 'letsfaego',
    //         'first_name' => 'kevin2',
    //         'last_name' => 'zhang',
    //         'user_name' => 'faeapp2',
    //         'gender' => 'male',
    //         'birthday' => '1992-02-02',
    //     );
    //     $server = array(
    //         'Accept' => 'application/x.faeapp.v1+json', 
    //         'Fae-Client-Version' => 'ios-0.0.1',
    //     );
    //     $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter2, [], [], $this->transformHeadersToServerVars($server));
    //     $this->refreshApplication();
    //     $parameters = array(
    //         'email' => 'letsfae@126.com', 
    //         'password' => 'letsfaego',
    //         'user_name' => 'faeapp',
    //     );
    //     $server1 = array(
    //         'Accept' => 'application/x.faeapp.v1+json', 
    //         'Fae-Client-Version' => 'ios-0.0.1',
    //     );
    //     //login of the user.
    //     $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
    //     $this->refreshApplication(); 
    //     $array = json_decode($login_response->getContent());
    //     $chat = Chats::create([
    //         'user_a_id' => 1,
    //         'user_b_id' => 2,
    //         'last_message_sender_id' => 3,
    //         'last_message' => 'Hello world',
    //         'last_message_type' => 'text',
    //         'user_a_unread_count' => 1,
    //         'user_b_unread_count' => 1,
    //         'last_message_timestamp' => '2016-07-16 22:19:17',
    //     ]); 
    //     $server2 = array(
    //         'Accept' => 'application/x.faeapp.v1+json', 
    //         'Fae-Client-Version' => 'ios-0.0.1',
    //         'Authorization' => 'FAE '.$array->debug_base64ed,
    //     );   
    //     $response = $this->call('get', 'http://'.$this->domain.'/chats/unread', [], [], [], $this->transformHeadersToServerVars($server2));   
    //     $this->seeJson([
    //              'message' => 'last message sender not found',
    //              'error_code' => '404-1',
    //              'status_code' => '404', 
    //     ]); 
    //     $result = false;
    //     if ($response->status() == '404') {
    //         $result = true;
    //     }
    //     $this->assertEquals(true, $result); 
    // }


    //test correct response of the method of marking read. 
    public function testMarkRead() { 
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
            'email' => 'letsfae@yahoo.com',
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
        $chat = Chats::create([
            'user_a_id' => 1,
            'user_b_id' => 2,
            'last_message_sender_id' => 1,
            'last_message' => 'Hello world',
            'last_message_type' => 'text',
            'user_a_unread_count' => 1,
            'user_b_unread_count' => 1,
            'last_message_timestamp' => '2016-07-16 22:19:17',
        ]); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters2 = array(
              'chat_id' => 1,
        );
        $response = $this->call('post', 'http://'.$this->domain.'/chats/read', $parameters2, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
        $this->seeInDatabase('chats', ['user_a_unread_count' => 0]);
    }

    //test whether the input format is right. 
    public function testMarkRead2() {   
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
            'email' => 'letsfae@yahoo.com',
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
        $chat = Chats::create([
            'user_a_id' => 1,
            'user_b_id' => 2,
            'last_message_sender_id' => 1,
            'last_message' => 'Hello world',
            'last_message_type' => 'text',
            'user_a_unread_count' => 1,
            'user_b_unread_count' => 1,
            'last_message_timestamp' => '2016-07-16 22:19:17',
        ]); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        //the input format of the chat_id is not correct.
        $parameters2 = array(
              'chat_id' => 'fae',
        );
        $response = $this->call('post', 'http://'.$this->domain.'/chats/read', $parameters2, [], [], $this->transformHeadersToServerVars($server2));    
        $array2 = json_decode($response->getContent());  
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Could not mark unread message.' && $array2->errors->chat_id[0] == 'The chat id must be an integer.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the response when the chat does not exits with the chat_id. 
    // public function testMarkRead3() {   
    //     // $this->markTestSkipped();
    //     $parameter1 = array(
    //         'email' => 'letsfae@126.com',
    //         'password' => 'letsfaego',
    //         'first_name' => 'kevin',
    //         'last_name' => 'zhang',
    //         'user_name' => 'faeapp',
    //         'gender' => 'male',
    //         'birthday' => '1992-02-02',
    //     );
    //     $server = array(
    //         'Accept' => 'application/x.faeapp.v1+json', 
    //         'Fae-Client-Version' => 'ios-0.0.1',
    //     );
    //     $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
    //     $this->refreshApplication();
    //     $parameter2 = array(
    //         'email' => 'letsfae@yahoo.com',
    //         'password' => 'letsfaego',
    //         'first_name' => 'kevin2',
    //         'last_name' => 'zhang',
    //         'user_name' => 'faeapp2',
    //         'gender' => 'male',
    //         'birthday' => '1992-02-02',
    //     );
    //     $server = array(
    //         'Accept' => 'application/x.faeapp.v1+json', 
    //         'Fae-Client-Version' => 'ios-0.0.1',
    //     );
    //     $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter2, [], [], $this->transformHeadersToServerVars($server));
    //     $this->refreshApplication();
    //     $parameters = array(
    //         'email' => 'letsfae@126.com', 
    //         'password' => 'letsfaego',
    //         'user_name' => 'faeapp',
    //     );
    //     $server1 = array(
    //         'Accept' => 'application/x.faeapp.v1+json', 
    //         'Fae-Client-Version' => 'ios-0.0.1',
    //     );
    //     //login of the user.
    //     $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
    //     $this->refreshApplication();
    //     $array = json_decode($login_response->getContent());
    //     $chat = Chats::create([
    //         'user_a_id' => 1,
    //         'user_b_id' => 2,
    //         'last_message_sender_id' => 1,
    //         'last_message' => 'Hello world',
    //         'last_message_type' => 'text',
    //         'user_a_unread_count' => 1,
    //         'user_b_unread_count' => 1,
    //         'last_message_timestamp' => '2016-07-16 22:19:17',
    //     ]); 
    //     $server2 = array(
    //         'Accept' => 'application/x.faeapp.v1+json', 
    //         'Fae-Client-Version' => 'ios-0.0.1',
    //         'Authorization' => 'FAE '.$array->debug_base64ed,
    //     );   
    //     //the chat_id does not exist in the chats database.
    //     $parameters2 = array(
    //           'chat_id' => 2,
    //     );
    //     $response = $this->call('post', 'http://'.$this->domain.'/chats/read', $parameters2, [], [], $this->transformHeadersToServerVars($server2));  
    //     $this->seeJson([
    //              'message' => 'chat not found',
    //              'error_code' => '404-2',
    //              'status_code' => '404', 
    //     ]); 
    //     $result = false;
    //     if ($response->status() == '404') {
    //         $result = true;
    //     }
    //     $this->assertEquals(true, $result);    
    // }

    //test the response when the user is not in the chat. 
    public function testMarkRead4() {   
        $this->markTestSkipped();
        $parameter1 = array(
            'email' => 'letsfae@yahoo.com',
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
        $response1 = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameter2 = array(
            'email' => 'letsfae2@yahoo.com',
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
        $response2 = $this->call('post', 'http://'.$this->domain.'/users', $parameter2, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameter3 = array(
            'email' => 'letsfae3@yahoo.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin3',
            'last_name' => 'zhang',
            'user_name' => 'faeapp3',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        );
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response3 = $this->call('post', 'http://'.$this->domain.'/users', $parameter3, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameters = array(
            'email' => 'letsfae3@yahoo.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp3',
        );
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $this->refreshApplication();
        $array = json_decode($login_response->getContent());
        $chat = Chats::create([
            'user_a_id' => 1,
            'user_b_id' => 2,
            'last_message_sender_id' => 1,
            'last_message' => 'Hello world',
            'last_message_type' => 'text',
            'user_a_unread_count' => 1,
            'user_b_unread_count' => 1,
            'last_message_timestamp' => '2016-07-16 22:19:17',
        ]); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );    
        $parameters2 = array(
              'chat_id' => 1,
        );
        $response = $this->call('post', 'http://'.$this->domain.'/chats/read', $parameters2, [], [], $this->transformHeadersToServerVars($server2));  
        $this->seeJson([
                 'message' => 'user not in this chat',
                 'error_code' => '403-1',
                 'status_code' => '403', 
        ]); 
        $result = false;
        if ($response->status() == '403') {
            $result = true;
        }
        $this->assertEquals(true, $result);    
    }

    //test correct response of the method of getting history. 
    public function testGetHistory() {   
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
            'email' => 'letsfae@yahoo.com',
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
        $chat = Chats::create([
            'user_a_id' => 1,
            'user_b_id' => 2,
            'last_message_sender_id' => 1,
            'last_message' => 'Hello world',
            'last_message_type' => 'text',
            'user_a_unread_count' => 1,
            'user_b_unread_count' => 1,
            'last_message_timestamp' => '2016-07-16 22:19:17',
        ]);
        $chat1 = Chats::create([
            'user_a_id' => 1,
            'user_b_id' => 2,
            'last_message_sender_id' => 1,
            'last_message' => 'Hello world2',
            'last_message_type' => 'text',
            'user_a_unread_count' => 1,
            'user_b_unread_count' => 1,
            'last_message_timestamp' => '2016-07-16 22:19:20',
        ]);
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $response = $this->call('get', 'http://'.$this->domain.'/chats', [], [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent());
        for ($i = 0; $i < 2; $i++) {
            $this->seeJson([
                    'chat_id' => $i + 1,
                    'with_user_id' => 2,
                    'with_user_name' => 'faeapp2',
                    'last_message' => $array2[0]->last_message,
                    'last_message_sender_id' => 1,
                    'last_message_sender_name' => 'faeapp',
                    'last_message_type' => 'text',
                    'last_message_timestamp' => $array2[0]->last_message_timestamp,
                    'unread_count' => 1,
                    'server_sent_timestamp' => $array2[0]->server_sent_timestamp
            ]);
        }
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test response when the last message sender is not found. 
    // public function testGetHistory2() {  
    //     $this->markTestSkipped();
    //     $parameter1 = array(
    //         'email' => 'letsfae@126.com',
    //         'password' => 'letsfaego',
    //         'first_name' => 'kevin',
    //         'last_name' => 'zhang',
    //         'user_name' => 'faeapp',
    //         'gender' => 'male',
    //         'birthday' => '1992-02-02',
    //     );
    //     $server = array(
    //         'Accept' => 'application/x.faeapp.v1+json', 
    //         'Fae-Client-Version' => 'ios-0.0.1',
    //     );
    //     $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
    //     $this->refreshApplication();
    //     $parameter2 = array(
    //         'email' => 'letsfae@yahoo.com',
    //         'password' => 'letsfaego',
    //         'first_name' => 'kevin2',
    //         'last_name' => 'zhang',
    //         'user_name' => 'faeapp2',
    //         'gender' => 'male',
    //         'birthday' => '1992-02-02',
    //     );
    //     $server = array(
    //         'Accept' => 'application/x.faeapp.v1+json', 
    //         'Fae-Client-Version' => 'ios-0.0.1',
    //     );
    //     $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter2, [], [], $this->transformHeadersToServerVars($server));
    //     $this->refreshApplication();
    //     $parameters = array(
    //         'email' => 'letsfae@126.com', 
    //         'password' => 'letsfaego',
    //         'user_name' => 'faeapp',
    //     );
    //     $server1 = array(
    //         'Accept' => 'application/x.faeapp.v1+json', 
    //         'Fae-Client-Version' => 'ios-0.0.1',
    //     );
    //     //login of the user.
    //     $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
    //     $this->refreshApplication();
    //     $array = json_decode($login_response->getContent());
    //     $chat = Chats::create([
    //         'user_a_id' => 1,
    //         'user_b_id' => 2,
    //         'last_message_sender_id' => 3,
    //         'last_message' => 'Hello world',
    //         'last_message_type' => 'text',
    //         'user_a_unread_count' => 1,
    //         'user_b_unread_count' => 1,
    //         'last_message_timestamp' => '2016-07-16 22:19:17',
    //     ]);
    //     $chat1 = Chats::create([
    //         'user_a_id' => 1,
    //         'user_b_id' => 2,
    //         'last_message_sender_id' => 3,
    //         'last_message' => 'Hello world2',
    //         'last_message_type' => 'text',
    //         'user_a_unread_count' => 1,
    //         'user_b_unread_count' => 1,
    //         'last_message_timestamp' => '2016-07-16 22:19:20',
    //     ]);
    //     $server2 = array(
    //         'Accept' => 'application/x.faeapp.v1+json', 
    //         'Fae-Client-Version' => 'ios-0.0.1',
    //         'Authorization' => 'FAE '.$array->debug_base64ed,
    //     );   
    //     $response = $this->call('get', 'http://'.$this->domain.'/chats', [], [], [], $this->transformHeadersToServerVars($server2));
    //     $this->seeJson([
    //              'message' => 'user not found',
    //              'error_code' => '404-3',
    //              'status_code' => '404', 
    //     ]); 
    //     $result = false;
    //     if ($response->status() == '404') {
    //         $result = true;
    //     }
    //     $this->assertEquals(true, $result); 
    // }
    //test correct response of the method of deleting message. 
    public function testDelete() {  
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
            'email' => 'letsfae@yahoo.com',
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
        $chat = Chats::create([
            'user_a_id' => 1,
            'user_b_id' => 2,
            'last_message_sender_id' => 1,
            'last_message' => 'Hello world',
            'last_message_type' => 'text',
            'user_a_unread_count' => 1,
            'user_b_unread_count' => 1,
            'last_message_timestamp' => '2016-07-16 22:19:17',
        ]); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        //delete the chat information of with the selected chat_id.
        $response = $this->call('delete', 'http://'.$this->domain.'/chats/1', [], [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent());  
        $this->assertResponseStatus(204);
    }

    //test whether the input format is right. 
    public function testDelete2() {  
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
            'email' => 'letsfae@yahoo.com',
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
        $chat = Chats::create([
            'user_a_id' => 1,
            'user_b_id' => 2,
            'last_message_sender_id' => 1,
            'last_message' => 'Hello world',
            'last_message_type' => 'text',
            'user_a_unread_count' => 1,
            'user_b_unread_count' => 1,
            'last_message_timestamp' => '2016-07-16 22:19:17',
        ]); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        //the chat_id does not exist in the chats database.
        $response = $this->call('delete', 'http://'.$this->domain.'/chats/fae', [], [], [], $this->transformHeadersToServerVars($server2));   
        $this->seeJson([
                 'message' => 'chat_id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test the response when the chat does not exists with the chat_id. 
    public function testDelete3() {  
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
            'email' => 'letsfae@yahoo.com',
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
        $chat = Chats::create([
            'user_a_id' => 1,
            'user_b_id' => 2,
            'last_message_sender_id' => 1,
            'last_message' => 'Hello world',
            'last_message_type' => 'text',
            'user_a_unread_count' => 1,
            'user_b_unread_count' => 1,
            'last_message_timestamp' => '2016-07-16 22:19:17',
        ]); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        //the chat_id does not exist in the chats database.
        $response = $this->call('delete', 'http://'.$this->domain.'/chats/2', [], [], [], $this->transformHeadersToServerVars($server2));   
        $this->seeJson([
                 'message' => 'chat not found',
                 'error_code' => '404-2',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }
    //test whether the user who have logged in have the right to delete this chat.
    public function testDelete4() {   
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
            'email' => 'letsfae@yahoo.com',
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
        $parameter3 = array(
            'email' => 'letsfae@gmial.com',
            'password' => 'letsfaego',
            'first_name' => 'kevin3',
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
        $chat = Chats::create([
            'user_a_id' => 2,
            'user_b_id' => 3,
            'last_message_sender_id' => 1,
            'last_message' => 'Hello world',
            'last_message_type' => 'text',
            'user_a_unread_count' => 1,
            'user_b_unread_count' => 1,
            'last_message_timestamp' => '2016-07-16 22:19:17',
        ]); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        //the user who have logged in is not with user_a_id or user_b_id.
        $response = $this->call('delete', 'http://'.$this->domain.'/chats/1', [], [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent());   
        $result = false;
        if ($response->status() == '401' && $array2->message == 'Bad request, you have no right to delete this chat') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test correct response of the method of getting getChatIdFromUserId. 
    public function testGetChatIdFromUserId() {   
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
            'email' => 'letsfae@yahoo.com',
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
        $chat = Chats::create([
            'user_a_id' => 1,
            'user_b_id' => 2,
            'last_message_sender_id' => 1,
            'last_message' => 'Hello world',
            'last_message_type' => 'text',
            'user_a_unread_count' => 1,
            'user_b_unread_count' => 1,
            'last_message_timestamp' => '2016-07-16 22:19:17',
        ]); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $response = $this->call('get', 'http://'.$this->domain.'/chats/users/1/2', [], [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->seeJson([
                'chat_id' => $array2->chat_id, 
        ]);
        
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test response when the format of the input user_a_id is not right.
    public function testGetChatIdFromUserId2() {   
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
            'email' => 'letsfae@yahoo.com',
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
        $chat = Chats::create([
            'user_a_id' => 1,
            'user_b_id' => 2,
            'last_message_sender_id' => 1,
            'last_message' => 'Hello world',
            'last_message_type' => 'text',
            'user_a_unread_count' => 1,
            'user_b_unread_count' => 1,
            'last_message_timestamp' => '2016-07-16 22:19:17',
        ]); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $response = $this->call('get', 'http://'.$this->domain.'/chats/users/fae/2', [], [], [], $this->transformHeadersToServerVars($server2));   
        $this->seeJson([
                 'message' => 'user_id is not integer',
                 'error_code' => '400-3',
                 'status_code' => '400', 
        ]); 
        $result = false;
        if ($response->status() == '400') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
    }

    //test response when the user does not exists.
    public function testGetChatIdFromUserId3() {   
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
            'email' => 'letsfae@yahoo.com',
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
        $chat = Chats::create([
            'user_a_id' => 1,
            'user_b_id' => 2,
            'last_message_sender_id' => 1,
            'last_message' => 'Hello world',
            'last_message_type' => 'text',
            'user_a_unread_count' => 1,
            'user_b_unread_count' => 1,
            'last_message_timestamp' => '2016-07-16 22:19:17',
        ]); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $response = $this->call('get', 'http://'.$this->domain.'/chats/users/2/3', [], [], [], $this->transformHeadersToServerVars($server2));   
        $this->seeJson([
                 'message' => 'user not in this chat',
                 'error_code' => '403-1',
                 'status_code' => '403', 
        ]); 
        $result = false;
        if ($response->status() == '403') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
    }
    //test response when the chat information does not exist with the given two user_id.
    public function testGetChatIdFromUserId4() {   
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
            'email' => 'letsfae@yahoo.com',
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
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $response = $this->call('get', 'http://'.$this->domain.'/chats/users/1/2', [], [], [], $this->transformHeadersToServerVars($server2));   
        $this->seeJson([
                 'message' => 'chat not found',
                 'error_code' => '404-2',
                 'status_code' => '404', 
        ]); 
        $result = false;
        if ($response->status() == '404') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
    }

    //test correct response of the method of getting getMessageByUserId. 
    public function testGetMessageByUserId() {   
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
            'email' => 'letsfae@yahoo.com',
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
        $chat = Chats::create([
            'user_a_id' => 1,
            'user_b_id' => 2,
            'last_message_sender_id' => 1,
            'last_message' => 'Hello world',
            'last_message_type' => 'text',
            'user_a_unread_count' => 1,
            'user_b_unread_count' => 1,
            'last_message_timestamp' => '2016-07-16 22:19:17',
        ]); 
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $response = $this->call('get', 'http://'.$this->domain.'/chats/1/2', [], [], [], $this->transformHeadersToServerVars($server2));    
        $array2 = json_decode($response->getContent()); 
        $this->seeJson([
                //待补充，没firebase
        ]);
        
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

}
