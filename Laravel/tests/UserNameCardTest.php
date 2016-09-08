<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Name_cards;
use App\Name_card_tags;

class UserNameCardTest extends TestCase {
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

    //test correct response of the method of getNameCard.
    public function testGetNameCard() { 
        // $this->markTestSkipped();   
        $parameter1 = array(
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $this->refreshApplication();
        $array = json_decode($login_response->getContent()); 
        $namecard = Name_cards::where('user_id', '=', 1)->first();
        $namecard->nick_name = 'kevin';
        $namecard->short_intro = 'this is a test';
        $namecard->tag_ids = '1;2';
        $namecard->save();
        $name_card_tags = new Name_card_tags;
        $name_card_tags->title = 'fae';
        $name_card_tags->color = '#fff000'; 
        $name_card_tags->save();
        $name_card_tags2 = new Name_card_tags;
        $name_card_tags2->title = 'app';
        $name_card_tags2->color = '#fff000';
        $name_card_tags2->save();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $response = $this->call('get', 'http://'.$this->domain.'/users/1/name_card', [], [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->seeJson([
                'nick_name' => 'kevin',
                'short_intro' => 'this is a test',
                'tags' => array(
                     ['tag_id' => 1,
                    'title' => 'fae',
                    'color' => '#fff000'],  
                    ['tag_id' => 2,
                    'title' => 'app',
                    'color' => '#fff000'],
                ),
                'gender' => 'male',
        ]);
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test the response when the tag_ids is null.
    public function testGetNameCard2() { 
        // $this->markTestSkipped();   
        $parameter1 = array(
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
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
        $response = $this->call('get', 'http://'.$this->domain.'/users/1/name_card', [], [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->seeJson([
                'nick_name' => null,
                'short_intro' => null,
                'tags' => [],
                'gender' => 'male',
        ]);
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test the response when the namecard does not exist.
    public function testGetNameCard3() { 
        // $this->markTestSkipped();   
        $parameter1 = array(
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
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
        //the namecard with the given user_id does not exist.
        $response = $this->call('get', 'http://'.$this->domain.'/users/2/name_card', [], [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '404' && $array2->message == 'Not Found') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test the correct response of the method of getSelfNameCard.
    public function testGetSelfNameCard() { 
        // $this->markTestSkipped();   
        $parameter1 = array(
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
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
        $response = $this->call('get', 'http://'.$this->domain.'/users/name_card', [], [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->seeJson([
                'nick_name' => null,
                'short_intro' => null,
                'tags' => [],
                'gender' => 'male',
        ]);
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test the correct response of the method of getAllTags.
    public function testGetAllTags() { 
        // $this->markTestSkipped();   
        $parameter1 = array(
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $this->refreshApplication();
        $array = json_decode($login_response->getContent());
        $name_card_tags = new Name_card_tags;
        $name_card_tags->title = 'fae';
        $name_card_tags->color = '#fff000'; 
        $name_card_tags->save();
        $name_card_tags2 = new Name_card_tags;
        $name_card_tags2->title = 'app';
        $name_card_tags2->color = '#fff000';
        $name_card_tags2->save();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $response = $this->call('get', 'http://'.$this->domain.'/users/name_card/tags', [], [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->seeJson([
                    ['tag_id' => 1,
                    'title' => 'fae',
                    'color' => '#fff000'],  
                    ['tag_id' => 2,
                    'title' => 'app',
                    'color' => '#fff000'],
        ]);
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test the response when the database of Name_card_tags is null.
    public function testGetAllTags2() { 
        // $this->markTestSkipped();   
        $parameter1 = array(
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
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
        $response = $this->call('get', 'http://'.$this->domain.'/users/name_card/tags', [], [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $this->seeJson([]);
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test the correct response of the method of updateNameCard.
    public function testUpdateNameCard() { 
        // $this->markTestSkipped();   
        $parameter1 = array(
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $this->refreshApplication();
        $array = json_decode($login_response->getContent());
        $namecard = Name_cards::where('user_id', '=', 1)->first();
        $namecard->nick_name = 'kevin';
        $namecard->short_intro = 'this is a test';
        $namecard->tag_ids = '1;2';
        $namecard->save(); 
        $name_card_tags = new Name_card_tags;
        $name_card_tags->title = 'fae';
        $name_card_tags->color = '#fff000'; 
        $name_card_tags->save();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters2 = array(
            'nick_name' => 'updatekevin',
            'short_intro' => 'this is the test of update',
            'tag_ids' => '1', 
        ); 
        $response = $this->call('post', 'http://'.$this->domain.'/users/name_card', $parameters2, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $result = false;
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
        $this->seeInDatabase('name_cards', ['nick_name' => 'updatekevin', 'short_intro' => 'this is the test of update', 'tag_ids' => '1']);
    }

    //test the response when the input parameter are all null.
    public function testUpdateNameCard2() { 
        // $this->markTestSkipped();   
        $parameter1 = array(
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $this->refreshApplication();
        $array = json_decode($login_response->getContent());
        $namecard = Name_cards::where('user_id', '=', 1)->first();
        $namecard->nick_name = 'kevin';
        $namecard->short_intro = 'this is a test';
        $namecard->tag_ids = '1;2';
        $namecard->save(); 
        $name_card_tags = new Name_card_tags;
        $name_card_tags->title = 'fae';
        $name_card_tags->color = '#fff000'; 
        $name_card_tags->save();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        $response = $this->call('post', 'http://'.$this->domain.'/users/name_card', [], [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent());
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Could not update name card.' && $array2->errors->nick_name[0] == 'The nick name field is required when none of short intro / tag ids are present.' && $array2->errors->short_intro[0] == 'The short intro field is required when none of nick name / tag ids are present.' && $array2->errors->tag_ids[0] == 'The tag ids field is required when none of nick name / short intro are present.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the response when the input of short_intro is empty and the other two parameters are null.
    public function testUpdateNameCard3() { 
        // $this->markTestSkipped();   
        $parameter1 = array(
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $this->refreshApplication();
        $array = json_decode($login_response->getContent());
        $namecard = Name_cards::where('user_id', '=', 1)->first();
        $namecard->nick_name = 'kevin';
        $namecard->short_intro = 'this is a test';
        $namecard->tag_ids = '1;2';
        $namecard->save(); 
        $name_card_tags = new Name_card_tags;
        $name_card_tags->title = 'fae';
        $name_card_tags->color = '#fff000'; 
        $name_card_tags->save();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        $parameters2 = array( 
            'short_intro' => '',  
        ); 
        $response = $this->call('post', 'http://'.$this->domain.'/users/name_card', $parameters2, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result);
        $this->seeInDatabase('name_cards', ['short_intro' => null]);
    }

    //test the response when the input of short_intro is empty and the format of nick_name is wrong.
    public function testUpdateNameCard4() { 
        // $this->markTestSkipped();   
        $parameter1 = array(
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $this->refreshApplication();
        $array = json_decode($login_response->getContent());
        $namecard = Name_cards::where('user_id', '=', 1)->first();
        $namecard->nick_name = 'kevin';
        $namecard->short_intro = 'this is a test';
        $namecard->tag_ids = '1;2';
        $namecard->save(); 
        $name_card_tags = new Name_card_tags;
        $name_card_tags->title = 'fae';
        $name_card_tags->color = '#fff000'; 
        $name_card_tags->save();
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );  
        //the format of the nick_name is wrong. 
        $parameters2 = array( 
            'short_intro' => '',
            'nick_name' => 'wrong_name_format'
        ); 
        $response = $this->call('post', 'http://'.$this->domain.'/users/name_card', $parameters2, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Could not update name card.' && $array2->errors->nick_name[0] == 'The nick name may only contain letters and numbers.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test the response when the tag with the tag_id is null in the database of Name_card_tags.
    public function testUpdateNameCard5() { 
        // $this->markTestSkipped();   
        $parameter1 = array(
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
        $response = $this->call('post', 'http://'.$this->domain.'/users', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $this->refreshApplication();
        $parameters = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'kevin',
        );
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $this->refreshApplication();
        $array = json_decode($login_response->getContent());
        $namecard = Name_cards::where('user_id', '=', 1)->first();
        $namecard->nick_name = 'kevin';
        $namecard->short_intro = 'this is a test';
        $namecard->tag_ids = '1;2';
        $namecard->save();  
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters2 = array( 
            'nick_name' => 'updatekevin',
            'short_intro' => 'this is the test of update',
            'tag_ids' => '1', 
        ); 
        $response = $this->call('post', 'http://'.$this->domain.'/users/name_card', $parameters2, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent());  
        $result = false;
        if ($response->status() == '400' && $array2->message == 'tag doest not exist') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
}
