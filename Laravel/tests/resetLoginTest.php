<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Users;

class resetLoginTest extends TestCase {
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
        $this->markTestSkipped(); 
    } 

    public function tearDown() {
        parent::tearDown();
    }

    //test the correct response of the method of sendResetCode.
    public function testSendResetCode() {
       //
        // $this->markTestSkipped();
        // $user = Users::create([
        //     'email' => 'letsfae@126.com',
        //     'password' => bcrypt('letsfaego'),
        //     'first_name' => 'kevin',
        //     'last_name' => 'zhang',
        //     'gender' => 'male',
        //     'birthday' => '1992-02-02',
        // ]);
        // $server = array(
        //     'Accept' => 'application/x.faeapp.v1+json', 
        //     'Fae-Client-Version' => 'ios-0.0.1',
        // );
        // $parameters1 = array(
        //     'email' => 'letsfae@126.com',
        // );
        // $response = $this->call('post', 'http://'.$this->domain.'/reset_login/code', $parameters1, [], [], $this->transformHeadersToServerVars($server));
        // var_dump($response);
    }   
}
