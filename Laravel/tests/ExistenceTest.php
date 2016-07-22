<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Users;

class ExistenceTest extends TestCase {
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
        $this->beforeApplicationDestroyed(function () {
            DB::disconnect();
        });
    }

    //the correct response of the method of checking whether the email exists!
    public function testEmailExistence() { 
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        ]);
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('get', 'http://'.$this->domain.'/existence/email/letsfae@126.com', [], [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($response->getContent());
        $this->seeJson([
                 'existence' => $array->existence,
        ]);
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
    }

  	//test whether the format of the email is right.
    public function testEmailExistence2() { 
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02',
        ]);
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //the wrong format of the email.
        $response = $this->call('get', 'http://'.$this->domain.'/existence/email/letsfae126.com', [], [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array->message == 'Bad request, Please verify your email format!') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
    }

    //the correct response of the method of checking whether the user_name exists!
    public function testUserNameExistence() { 
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'user_name' => 'kevin',
        ]);
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        $response = $this->call('get', 'http://'.$this->domain.'/existence/user_name/kevin', [], [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($response->getContent());
        $this->seeJson([
                 'existence' => $array->existence,
        ]);
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
    }

    //test whether the format of the user_name is right.
    public function testUserNameExistence2() { 
        $user = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'user_name' => 'kevin',
        ]);
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //the wrong format of the user_name.
        $response = $this->call('get', 'http://'.$this->domain.'/existence/user_name/kevinkevinkevinkevinkevinkevinkevinkevinkevinkevinkevin', [], [], [], $this->transformHeadersToServerVars($server));
        $array = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '403' && $array->message == 'Bad request, Please verify your user_name format!') {
            $result = true;
        }
        $this->assertEquals(true, $result);  
    }
}
