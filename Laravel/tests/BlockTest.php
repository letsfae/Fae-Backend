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
        $user2 = Users::create([
            'email' => 'letsfae2@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp2',
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

    // the correct response of the add block.
    public function testDelete() { 
        $this->markTestSkipped(); 
        //register of the user.
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
        $user2 = Users::create([
            'email' => 'letsfae2@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp2',
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
        if ($response->status() == '204') {
            $result = true;
        }  
        $this->assertEquals(true, $result);  
        $this->notSeeInDatabase('blocks', ['user_id' => 1, 'block_id' => 2]);
    }
}

