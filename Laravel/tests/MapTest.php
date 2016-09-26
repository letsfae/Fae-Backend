<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\Geometry;
use App\Users;
use App\Sessions;
use App\Comments;
use App\Medias;
use App\Faevors;
use App\Tags;
use App\Files;
use App\ChatRooms;

class MapTest extends TestCase
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
        $this->beforeApplicationDestroyed(function () {
            DB::disconnect();
        });
        parent::tearDown();
    }

    // the correct response of the method of getMap.
    public function testGetMap() { 
        // $this->markTestSkipped(); 
        //register of the user.
        for ($i = 1; $i < 11; $i++) {  
            ${'user' . $i} = Users::create([
                'email' => 'letsfae'.$i.'@126.com',
                'password' => bcrypt('letsfaego'),
                'first_name' => 'kevin',
                'last_name' => 'zhang',
                'user_name' => 'faeapp'.$i,
                'gender' => 'male',
                'birthday' => '1992-02-02',
                'login_count' => 0, 
            ]);
        }
        for ($i = 1; $i < 11; $i++) { 
            ${'parameter' . $i}  = array(
            'email' => 'letsfae'.$i.'@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp'.$i,
            );
        } 
        
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user. 
        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 11; $i++) {
            $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', ${'parameter' . $i}, [], [], $this->transformHeadersToServerVars($server));
            $session1 = Sessions::where('user_id', '=', $i)->first();
            $session1->location = new Point($latitude,$longitude);
            $session1->save(); 
            $latitude = number_format($latitude + 0.000001, 6); 
            $this->refreshApplication();
        } 
        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 11; $i++) {
            ${'comment' . $i} = Comments::create([
                'user_id' => 1,
                'content' => 'This is the test'.$i,
                'geolocation' => new Point($latitude,$longitude), 
            ]); 
            $latitude = number_format($latitude + 0.000001, 6); 
        }

        $tags = new Tags;
        $tags->title = 'fae1';
        $tags->color = '#fff000';
        $tags->user_id = 1;  
        $tags->reference_count = 1;
        $tags->save();

        $tags = new Tags;
        $tags->title = 'fae2';
        $tags->color = '#fff000';
        $tags->user_id = 1;  
        $tags->reference_count = 1;
        $tags->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();

        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 4; $i++) {
            ${'media' . $i} = Medias::create([
                'user_id' => 1,
                'description' => 'This is the test'.$i,
                'geolocation' => new Point($latitude,$longitude), 
                'tag_ids' => '1;2',
                'file_ids' => '1;2'
            ]);  
            $latitude = number_format($latitude + 0.000001, 6); 
        }

        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 4; $i++) {
            ${'faevor' . $i} = Faevors::create([
                'user_id' => 1,
                'description' => 'This is the test'.$i,
                'geolocation' => new Point($latitude,$longitude),
                'name' => 'fae',
                'budget' => 100,
                'expire_time' => '2016-06-08 22:22:39',
                'due_time' => '2016-06-08 21:22:39',
                'tag_ids' => '1;2' 
            ]); 
            $latitude = number_format($latitude + 0.000001, 6); 
        }
        
        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 5; $i++) {
            ${'chatRoom' . $i} = ChatRooms::create([
                'user_id' => 1,
                'title' => 'faeapp',
                'geolocation' => new Point($latitude,$longitude),   
            ]); 
            $latitude = number_format($latitude + 0.000001, 6); 
        }

        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 

        $parameters = array(
            'geo_latitude' => 34.031958,
            'geo_longitude' => -118.288125, 
        );
        //get the map data. 
        $response = $this->call('get', 'http://'.$this->domain.'/map', $parameters, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent());   
        for ($i = 0; $i < 10; $i++) {
            $this->seeJson([  
                        'type' => 'user',
                        'user_id' => ($i + 1), 
                        'geolocation' => array(
                            array(
                            'latitude' => $array2[$i]->geolocation[0]->latitude,
                            'longitude' => $array2[$i]->geolocation[0]->longitude,
                            ),
                            array(
                            'latitude' => $array2[$i]->geolocation[1]->latitude,
                            'longitude' => $array2[$i]->geolocation[1]->longitude,
                            ),
                            array(
                            'latitude' => $array2[$i]->geolocation[2]->latitude,
                            'longitude' => $array2[$i]->geolocation[2]->longitude,
                            ),
                            array(
                            'latitude' => $array2[$i]->geolocation[3]->latitude,
                            'longitude' => $array2[$i]->geolocation[3]->longitude,
                            ),
                            array(
                            'latitude' => $array2[$i]->geolocation[4]->latitude,
                            'longitude' => $array2[$i]->geolocation[4]->longitude,
                            ),
                        ),
                        'created_at' => $array2[$i]->created_at, 
            ]);
        } 
        for ($i = 1; $i < 11; $i++) {
            $this->seeJson([  
                        'type' => 'comment',
                        'comment_id' => $i,
                        'user_id' => 1, 
                        'content' => 'This is the test'.$i,
                        'geolocation' => array( 
                            'latitude' => $array2[(9+$i)]->geolocation->latitude,
                            'longitude' => $array2[(9+$i)]->geolocation->longitude,
                        ),
                        'created_at' => $array2[(9+$i)]->created_at, 
            ]); 
        } 
        for ($i = 1; $i < 4; $i++) {
            $this->seeJson([  
                        'type' => 'media',
                        'media_id' => $i,
                        'user_id' => 1, 
                        'file_ids' => array('1','2'),
                        'tag_ids' => array('1','2'),
                        'description' => 'This is the test'.$i,
                        'geolocation' => array( 
                            'latitude' => $array2[(19+$i)]->geolocation->latitude,
                            'longitude' => $array2[(19+$i)]->geolocation->longitude,
                        ),
                        'created_at' => $array2[(19+$i)]->created_at, 
            ]); 
        } 
        for ($i = 1; $i < 4; $i++) {
            $this->seeJson([  
                        'type' => 'faevor',
                        'faevor_id' => $i,
                        'user_id' => 1, 
                        'file_ids' => null,
                        'tag_ids' => array('1','2'),
                        'description' => 'This is the test'.$i,
                        'name' => 'fae',
                        'budget' => 100,
                        'bonus' => null,
                        'due_time' => '2016-06-08 21:22:39',
                        'expire_time' => '2016-06-08 22:22:39',
                        'geolocation' => array( 
                            'latitude' => $array2[(22+$i)]->geolocation->latitude,
                            'longitude' => $array2[(22+$i)]->geolocation->longitude,
                        ),
                        'created_at' => $array2[(22+$i)]->created_at, 
            ]); 
        } 
        for ($i = 1; $i < 5; $i++) {
            $this->seeJson([   
                        'user_id' => 1, 
                        'title' => 'faeapp', 
                        'geolocation' => array( 
                            'latitude' => $array2[(25+$i)]->geolocation->latitude,
                            'longitude' => $array2[(25+$i)]->geolocation->longitude,
                        ),
                        'created_at' => $array2[(25+$i)]->created_at, 
            ]); 
        }
        $this->seeJson([]);
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    //test whether the input format is right.
    public function testGetMap2() { 
        // $this->markTestSkipped(); 
        //register of the user.
        $user1 = Users::create([
            'email' => 'letsfae@126.com',
            'password' => bcrypt('letsfaego'),
            'first_name' => 'kevin',
            'last_name' => 'zhang',
            'user_name' => 'faeapp',
            'gender' => 'male',
            'birthday' => '1992-02-02',
            'login_count' => 0, 
        ]);
        
        $parameter1 = array(
            'email' => 'letsfae@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp',
        );
         
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameter1, [], [], $this->transformHeadersToServerVars($server));
        $session1 = Sessions::where('user_id', '=', 1)->first();
        $session1->location = new Point(34.031958,-118.288125);
        $session1->save();

        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        //wrong format of the geo_longitude.
        $parameters = array(
            'geo_latitude' => 34.031958,
            'geo_longitude' => -218.288125, 
        );
        //get the map data. 
        $response = $this->call('get', 'http://'.$this->domain.'/map', $parameters, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent()); 
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Could not get map.' && $array2->errors->geo_longitude[0] == 'The geo longitude must be between -180 and 180.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the response when the filter of radius is given.
    public function testGetMap3() { 
        // $this->markTestSkipped(); 
        //register of the user.
        for ($i = 1; $i < 11; $i++) {  
            ${'user' . $i} = Users::create([
                'email' => 'letsfae'.$i.'@126.com',
                'password' => bcrypt('letsfaego'),
                'first_name' => 'kevin',
                'last_name' => 'zhang',
                'user_name' => 'faeapp'.$i,
                'gender' => 'male',
                'birthday' => '1992-02-02',
                'login_count' => 0, 
            ]);
        }
        for ($i = 1; $i < 11; $i++) { 
            ${'parameter' . $i}  = array(
            'email' => 'letsfae'.$i.'@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp'.$i,
            );
        } 
        
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user. 
        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 11; $i++) {
            $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', ${'parameter' . $i}, [], [], $this->transformHeadersToServerVars($server));
            $session1 = Sessions::where('user_id', '=', $i)->first();
            $session1->location = new Point($latitude,$longitude);
            $session1->save(); 
            $latitude = number_format($latitude + 0.000001, 6); 
            $this->refreshApplication();
        } 
        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 11; $i++) {
            ${'comment' . $i} = Comments::create([
                'user_id' => 1,
                'content' => 'This is the test'.$i,
                'geolocation' => new Point($latitude,$longitude), 
            ]); 
            $latitude = number_format($latitude + 0.000001, 6); 
        }

        $tags = new Tags;
        $tags->title = 'fae1';
        $tags->color = '#fff000';
        $tags->user_id = 1;  
        $tags->reference_count = 1;
        $tags->save();

        $tags = new Tags;
        $tags->title = 'fae2';
        $tags->color = '#fff000';
        $tags->user_id = 1;  
        $tags->reference_count = 1;
        $tags->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();
        //only three medias are in the radius.
        $media1 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125), 
            'tag_ids' => '1;2',
            'file_ids' => '1;2'
        ]);  

        $media2 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test2',
            'geolocation' => new Point(34.031959,-118.288125), 
            'tag_ids' => '1;2',
            'file_ids' => '1;2'
        ]); 

        $media3 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test3',
            'geolocation' => new Point(34.031960,-118.288125), 
            'tag_ids' => '1;2',
            'file_ids' => '1;2'
        ]); 

        $media4 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test4',
            'geolocation' => new Point(60.031961,-118.288125), 
            'tag_ids' => '1;2',
            'file_ids' => '1;2'
        ]); 

        $media5 = Medias::create([
            'user_id' => 1,
            'description' => 'This is the test5',
            'geolocation' => new Point(60.031962,-118.288125), 
            'tag_ids' => '1;2',
            'file_ids' => '1;2'
        ]);  
        //only three faevors are in the radius.
        $faevor1 = Faevors::create([
            'user_id' => 1,
            'description' => 'This is the test1',
            'geolocation' => new Point(34.031958,-118.288125),
            'name' => 'fae',
            'budget' => 100,
            'expire_time' => '2016-06-08 22:22:39',
            'due_time' => '2016-06-08 21:22:39',
            'tag_ids' => '1;2' 
        ]); 

        $faevor2 = Faevors::create([
            'user_id' => 1,
            'description' => 'This is the test2',
            'geolocation' => new Point(34.031959,-118.288125),
            'name' => 'fae',
            'budget' => 100,
            'expire_time' => '2016-06-08 22:22:39',
            'due_time' => '2016-06-08 21:22:39',
            'tag_ids' => '1;2' 
        ]); 

        $faevor3 = Faevors::create([
            'user_id' => 1,
            'description' => 'This is the test3',
            'geolocation' => new Point(34.031960,-118.288125),
            'name' => 'fae',
            'budget' => 100,
            'expire_time' => '2016-06-08 22:22:39',
            'due_time' => '2016-06-08 21:22:39',
            'tag_ids' => '1;2' 
        ]); 

        $faevor4 = Faevors::create([
            'user_id' => 1,
            'description' => 'This is the test4',
            'geolocation' => new Point(60.031961,-118.288125),
            'name' => 'fae',
            'budget' => 100,
            'expire_time' => '2016-06-08 22:22:39',
            'due_time' => '2016-06-08 21:22:39',
            'tag_ids' => '1;2' 
        ]); 

        $faevor5 = Faevors::create([
            'user_id' => 1,
            'description' => 'This is the test5',
            'geolocation' => new Point(60.031962,-188.288125),
            'name' => 'fae',
            'budget' => 100,
            'expire_time' => '2016-06-08 22:22:39',
            'due_time' => '2016-06-08 21:22:39',
            'tag_ids' => '1;2' 
        ]); 

        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        //the radius is given.
        $parameters = array(
            'geo_latitude' => 34.031958,
            'geo_longitude' => -118.288125, 
            'radius' => 10
        );
        //get the map data. 
        $response = $this->call('get', 'http://'.$this->domain.'/map', $parameters, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent());   
        for ($i = 0; $i < 10; $i++) {
            $this->seeJson([  
                        'type' => 'user',
                        'user_id' => ++$i, 
                        'geolocation' => array(
                            array(
                            'latitude' => $array2[$i]->geolocation[0]->latitude,
                            'longitude' => $array2[$i]->geolocation[0]->longitude,
                            ),
                            array(
                            'latitude' => $array2[$i]->geolocation[1]->latitude,
                            'longitude' => $array2[$i]->geolocation[1]->longitude,
                            ),
                            array(
                            'latitude' => $array2[$i]->geolocation[2]->latitude,
                            'longitude' => $array2[$i]->geolocation[2]->longitude,
                            ),
                            array(
                            'latitude' => $array2[$i]->geolocation[3]->latitude,
                            'longitude' => $array2[$i]->geolocation[3]->longitude,
                            ),
                            array(
                            'latitude' => $array2[$i]->geolocation[4]->latitude,
                            'longitude' => $array2[$i]->geolocation[4]->longitude,
                            ),
                        ),
                        'created_at' => $array2[$i]->created_at, 
            ]);
        }
        for ($i = 1; $i < 11; $i++) {
            $this->seeJson([  
                        'type' => 'comment',
                        'comment_id' => $i,
                        'user_id' => $i, 
                        'content' => 'This is the test'.$i,
                        'geolocation' => array( 
                            'latitude' => $array2[(9+$i)]->geolocation->latitude,
                            'longitude' => $array2[(9+$i)]->geolocation->longitude,
                        ),
                        'created_at' => $array2[(9+$i)]->created_at, 
            ]);
        }
        for ($i = 1; $i < 4; $i++) {
            $this->seeJson([  
                        'type' => 'media',
                        'media_id' => $i,
                        'user_id' => $i, 
                        'file_ids' => array('1','2'),
                        'tag_ids' => array('1','2'),
                        'description' => 'This is the test'.$i,
                        'geolocation' => array( 
                            'latitude' => $array2[(19+$i)]->geolocation->latitude,
                            'longitude' => $array2[(19+$i)]->geolocation->longitude,
                        ),
                        'created_at' => $array2[(19+$i)]->created_at, 
            ]);
        }
        for ($i = 1; $i < 4; $i++) {
            $this->seeJson([  
                        'type' => 'faevor',
                        'faevor_id' => $i,
                        'user_id' => $i, 
                        'file_ids' => null,
                        'tag_ids' => array('1','2'),
                        'description' => 'This is the test'.$i,
                        'name' => 'fae',
                        'budget' => 100,
                        'bonus' => null,
                        'due_time' => '2016-06-08 21:22:39',
                        'expire_time' => '2016-06-08 22:22:39',
                        'geolocation' => array( 
                            'latitude' => $array2[(22+$i)]->geolocation->latitude,
                            'longitude' => $array2[(22+$i)]->geolocation->longitude,
                        ),
                        'created_at' => $array2[(22+$i)]->created_at, 
            ]);
        }
        $this->seeJson([]);
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the response when the filter of type is given.
    public function testGetMap4() { 
        // $this->markTestSkipped(); 
        //register of the user.
        for ($i = 1; $i < 11; $i++) {  
            ${'user' . $i} = Users::create([
                'email' => 'letsfae'.$i.'@126.com',
                'password' => bcrypt('letsfaego'),
                'first_name' => 'kevin',
                'last_name' => 'zhang',
                'user_name' => 'faeapp'.$i,
                'gender' => 'male',
                'birthday' => '1992-02-02',
                'login_count' => 0, 
            ]);
        }
        for ($i = 1; $i < 11; $i++) { 
            ${'parameter' . $i}  = array(
            'email' => 'letsfae'.$i.'@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp'.$i,
            );
        } 
        
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user. 
        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 11; $i++) {
            $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', ${'parameter' . $i}, [], [], $this->transformHeadersToServerVars($server));
            $session1 = Sessions::where('user_id', '=', $i)->first();
            $session1->location = new Point($latitude,$longitude);
            $session1->save(); 
            $latitude = number_format($latitude + 0.000001, 6); 
            $this->refreshApplication();
        } 
        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 11; $i++) {
            ${'comment' . $i} = Comments::create([
                'user_id' => 1,
                'content' => 'This is the test'.$i,
                'geolocation' => new Point($latitude,$longitude), 
            ]); 
            $latitude = number_format($latitude + 0.000001, 6); 
        }

        $tags = new Tags;
        $tags->title = 'fae1';
        $tags->color = '#fff000';
        $tags->user_id = 1;  
        $tags->reference_count = 1;
        $tags->save();

        $tags = new Tags;
        $tags->title = 'fae2';
        $tags->color = '#fff000';
        $tags->user_id = 1;  
        $tags->reference_count = 1;
        $tags->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();

        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 4; $i++) {
            ${'media' . $i} = Medias::create([
                'user_id' => 1,
                'description' => 'This is the test'.$i,
                'geolocation' => new Point($latitude,$longitude), 
                'tag_ids' => '1;2',
                'file_ids' => '1;2'
            ]);  
            $latitude = number_format($latitude + 0.000001, 6); 
        }

        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 4; $i++) {
            ${'faevor' . $i} = Faevors::create([
                'user_id' => 1,
                'description' => 'This is the test'.$i,
                'geolocation' => new Point($latitude,$longitude),
                'name' => 'fae',
                'budget' => 100,
                'expire_time' => '2016-06-08 22:22:39',
                'due_time' => '2016-06-08 21:22:39',
                'tag_ids' => '1;2' 
            ]); 
            $latitude = number_format($latitude + 0.000001, 6); 
        }
        
        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 5; $i++) {
            ${'chatRoom' . $i} = ChatRooms::create([
                'user_id' => 1,
                'title' => 'faeapp',
                'geolocation' => new Point($latitude,$longitude),   
            ]); 
            $latitude = number_format($latitude + 0.000001, 6); 
        }

        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        //the type is given.
        $parameters = array(
            'geo_latitude' => 34.031958,
            'geo_longitude' => -118.288125, 
            'radius' => 10,
            'type' => 'user,comment'
        );
        //get the map data. 
        $response = $this->call('get', 'http://'.$this->domain.'/map', $parameters, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent());  
        for ($i = 0; $i < 10; $i++) {
            $this->seeJson([  
                        'type' => 'user',
                        'user_id' => ++$i, 
                        'geolocation' => array(
                            array(
                            'latitude' => $array2[$i]->geolocation[0]->latitude,
                            'longitude' => $array2[$i]->geolocation[0]->longitude,
                            ),
                            array(
                            'latitude' => $array2[$i]->geolocation[1]->latitude,
                            'longitude' => $array2[$i]->geolocation[1]->longitude,
                            ),
                            array(
                            'latitude' => $array2[$i]->geolocation[2]->latitude,
                            'longitude' => $array2[$i]->geolocation[2]->longitude,
                            ),
                            array(
                            'latitude' => $array2[$i]->geolocation[3]->latitude,
                            'longitude' => $array2[$i]->geolocation[3]->longitude,
                            ),
                            array(
                            'latitude' => $array2[$i]->geolocation[4]->latitude,
                            'longitude' => $array2[$i]->geolocation[4]->longitude,
                            ),
                        ),
                        'created_at' => $array2[$i]->created_at, 
            ]);
        }
        for ($i = 1; $i < 11; $i++) {
            $this->seeJson([  
                        'type' => 'comment',
                        'comment_id' => $i,
                        'user_id' => $i, 
                        'content' => 'This is the test'.$i,
                        'geolocation' => array( 
                            'latitude' => $array2[(9+$i)]->geolocation->latitude,
                            'longitude' => $array2[(9+$i)]->geolocation->longitude,
                        ),
                        'created_at' => $array2[(9+$i)]->created_at, 
            ]); 
        }
        $this->seeJson([]);
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the response when the filter of max_count is given.
    public function testGetMap5() { 
        // $this->markTestSkipped(); 
        //register of the user.
        for ($i = 1; $i < 11; $i++) {  
            ${'user' . $i} = Users::create([
                'email' => 'letsfae'.$i.'@126.com',
                'password' => bcrypt('letsfaego'),
                'first_name' => 'kevin',
                'last_name' => 'zhang',
                'user_name' => 'faeapp'.$i,
                'gender' => 'male',
                'birthday' => '1992-02-02',
                'login_count' => 0, 
            ]);
        }
        for ($i = 1; $i < 11; $i++) { 
            ${'parameter' . $i}  = array(
            'email' => 'letsfae'.$i.'@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp'.$i,
            );
        } 
        
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user. 
        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 11; $i++) {
            $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', ${'parameter' . $i}, [], [], $this->transformHeadersToServerVars($server));
            $session1 = Sessions::where('user_id', '=', $i)->first();
            $session1->location = new Point($latitude,$longitude);
            $session1->save(); 
            $latitude = number_format($latitude + 0.000001, 6); 
            $this->refreshApplication();
        } 
        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 11; $i++) {
            ${'comment' . $i} = Comments::create([
                'user_id' => 1,
                'content' => 'This is the test'.$i,
                'geolocation' => new Point($latitude,$longitude), 
            ]); 
            $latitude = number_format($latitude + 0.000001, 6); 
        }

        $tags = new Tags;
        $tags->title = 'fae1';
        $tags->color = '#fff000';
        $tags->user_id = 1;  
        $tags->reference_count = 1;
        $tags->save();

        $tags = new Tags;
        $tags->title = 'fae2';
        $tags->color = '#fff000';
        $tags->user_id = 1;  
        $tags->reference_count = 1;
        $tags->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();

        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 4; $i++) {
            ${'media' . $i} = Medias::create([
                'user_id' => 1,
                'description' => 'This is the test'.$i,
                'geolocation' => new Point($latitude,$longitude), 
                'tag_ids' => '1;2',
                'file_ids' => '1;2'
            ]);  
            $latitude = number_format($latitude + 0.000001, 6); 
        }

        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 4; $i++) {
            ${'faevor' . $i} = Faevors::create([
                'user_id' => 1,
                'description' => 'This is the test'.$i,
                'geolocation' => new Point($latitude,$longitude),
                'name' => 'fae',
                'budget' => 100,
                'expire_time' => '2016-06-08 22:22:39',
                'due_time' => '2016-06-08 21:22:39',
                'tag_ids' => '1;2' 
            ]); 
            $latitude = number_format($latitude + 0.000001, 6); 
        }
        
        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 5; $i++) {
            ${'chatRoom' . $i} = ChatRooms::create([
                'user_id' => 1,
                'title' => 'faeapp',
                'geolocation' => new Point($latitude,$longitude),   
            ]); 
            $latitude = number_format($latitude + 0.000001, 6); 
        }

        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        //the type is given.
        $parameters = array(
            'geo_latitude' => 34.031958,
            'geo_longitude' => -118.288125, 
            'radius' => 10,
            'type' => 'user,comment',
            'max_count' => 10
        );
        //get the map data. 
        $response = $this->call('get', 'http://'.$this->domain.'/map', $parameters, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent());  
        for ($i = 0; $i < 10; $i++) {
            $this->seeJson([  
                        'type' => 'user',
                        'user_id' => ++$i, 
                        'geolocation' => array(
                            array(
                            'latitude' => $array2[$i]->geolocation[0]->latitude,
                            'longitude' => $array2[$i]->geolocation[0]->longitude,
                            ),
                            array(
                            'latitude' => $array2[$i]->geolocation[1]->latitude,
                            'longitude' => $array2[$i]->geolocation[1]->longitude,
                            ),
                            array(
                            'latitude' => $array2[$i]->geolocation[2]->latitude,
                            'longitude' => $array2[$i]->geolocation[2]->longitude,
                            ),
                            array(
                            'latitude' => $array2[$i]->geolocation[3]->latitude,
                            'longitude' => $array2[$i]->geolocation[3]->longitude,
                            ),
                            array(
                            'latitude' => $array2[$i]->geolocation[4]->latitude,
                            'longitude' => $array2[$i]->geolocation[4]->longitude,
                            ),
                        ),
                        'created_at' => $array2[$i]->created_at, 
            ]);
        } 
        $this->seeJson([]);
        $result = false;
        if ($response->status() == '200') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }

    // test the response when the type wrong.
    public function testGetMap6() { 
        // $this->markTestSkipped(); 
        //register of the user.
        for ($i = 1; $i < 11; $i++) {  
            ${'user' . $i} = Users::create([
                'email' => 'letsfae'.$i.'@126.com',
                'password' => bcrypt('letsfaego'),
                'first_name' => 'kevin',
                'last_name' => 'zhang',
                'user_name' => 'faeapp'.$i,
                'gender' => 'male',
                'birthday' => '1992-02-02',
                'login_count' => 0, 
            ]);
        }
        for ($i = 1; $i < 11; $i++) { 
            ${'parameter' . $i}  = array(
            'email' => 'letsfae'.$i.'@126.com', 
            'password' => 'letsfaego',
            'user_name' => 'faeapp'.$i,
            );
        } 
        
        $server = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
        );
        //login of the user. 
        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 11; $i++) {
            $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', ${'parameter' . $i}, [], [], $this->transformHeadersToServerVars($server));
            $session1 = Sessions::where('user_id', '=', $i)->first();
            $session1->location = new Point($latitude,$longitude);
            $session1->save(); 
            $latitude = number_format($latitude + 0.000001, 6); 
            $this->refreshApplication();
        } 
        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 11; $i++) {
            ${'comment' . $i} = Comments::create([
                'user_id' => 1,
                'content' => 'This is the test'.$i,
                'geolocation' => new Point($latitude,$longitude), 
            ]); 
            $latitude = number_format($latitude + 0.000001, 6); 
        }

        $tags = new Tags;
        $tags->title = 'fae1';
        $tags->color = '#fff000';
        $tags->user_id = 1;  
        $tags->reference_count = 1;
        $tags->save();

        $tags = new Tags;
        $tags->title = 'fae2';
        $tags->color = '#fff000';
        $tags->user_id = 1;  
        $tags->reference_count = 1;
        $tags->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'video';
        $files->mine_type = 'video';
        $files->size = 256;
        $files->hash = 'test';
        $files->directory = 'test';
        $files->file_name_storage = 'test';
        $files->file_name = 'test';
        $files->reference_count = 0;
        $files->save();

        $files = new Files;
        $files->user_id = 1;
        $files->type = 'image';
        $files->mine_type = 'image';
        $files->size = 256;
        $files->hash = 'test1';
        $files->directory = 'test1';
        $files->file_name_storage = 'test1';
        $files->file_name = 'test1';
        $files->reference_count = 0;
        $files->save();

        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 4; $i++) {
            ${'media' . $i} = Medias::create([
                'user_id' => 1,
                'description' => 'This is the test'.$i,
                'geolocation' => new Point($latitude,$longitude), 
                'tag_ids' => '1;2',
                'file_ids' => '1;2'
            ]);  
            $latitude = number_format($latitude + 0.000001, 6); 
        }

        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 4; $i++) {
            ${'faevor' . $i} = Faevors::create([
                'user_id' => 1,
                'description' => 'This is the test'.$i,
                'geolocation' => new Point($latitude,$longitude),
                'name' => 'fae',
                'budget' => 100,
                'expire_time' => '2016-06-08 22:22:39',
                'due_time' => '2016-06-08 21:22:39',
                'tag_ids' => '1;2' 
            ]); 
            $latitude = number_format($latitude + 0.000001, 6); 
        }
        
        $latitude = 34.031958;
        $longitude = -118.288125;
        for ($i = 1; $i < 5; $i++) {
            ${'chatRoom' . $i} = ChatRooms::create([
                'user_id' => 1,
                'title' => 'faeapp',
                'geolocation' => new Point($latitude,$longitude),   
            ]); 
            $latitude = number_format($latitude + 0.000001, 6); 
        }

        $array = json_decode($login_response->getContent());
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1', 
            'Authorization' => 'FAE '.$array->debug_base64ed,
        ); 
        //the type is wrong.
        $parameters = array(
            'geo_latitude' => 34.031958,
            'geo_longitude' => -118.288125, 
            'radius' => 10,
            'type' => 'wrong',
            'max_count' => 10
        );
        //get the map data. 
        $response = $this->call('get', 'http://'.$this->domain.'/map', $parameters, [], [], $this->transformHeadersToServerVars($server2));
        $array2 = json_decode($response->getContent()); 
        $result = false;
        if ($response->status() == '404' && $array2->message == 'Not Found') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
    }

    //test correct response of the method of updateUserLocation. 
    public function testUpdateUserLocation() {
        // $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $session1 = Sessions::where('user_id', '=', 1)->first();
        $session1->is_mobile = true;
        $session1->save(); 
        $array = json_decode($login_response->getContent());   
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        $parameters = array(
            'geo_latitude' => 34.031960,
            'geo_longitude' => -118.288125,
        );
        $response = $this->call('post', 'http://'.$this->domain.'/map/user', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent());
        if ($response->status() == '201') {
            $result = true;
        }
        $this->assertEquals(true, $result); 
        $this->seeInDatabase('sessions', ['location' => '0101000020E61000003D0AD7A370925DC0DC63E94317044140']);
    }

    //test whether the input format is right.
    public function testUpdateUserLocation2() {
        // $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $session1 = Sessions::where('user_id', '=', 1)->first();
        $session1->is_mobile = true;
        $session1->save(); 
        $array = json_decode($login_response->getContent());   
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );   
        //the wrong format of the geo_longitude.
        $parameters = array(
            'geo_latitude' => 34.031960,
            'geo_longitude' => -218.288125,
        );
        $response = $this->call('post', 'http://'.$this->domain.'/map/user', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $result = false;
        if ($response->status() == '422' && $array2->message == 'Could not update user location.' && $array2->errors->geo_longitude[0] == 'The geo longitude must be between -180 and 180.') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
 
    //test the response when the is_mobile is false.
    public function testUpdateUserLocation3() {
        // $this->markTestSkipped();
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
        $server1 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
        );
        //login of the user.
        $login_response = $this->call('post', 'http://'.$this->domain.'/authentication', $parameters, [], [], $this->transformHeadersToServerVars($server1));
        $session1 = Sessions::where('user_id', '=', 1)->first(); 
        $array = json_decode($login_response->getContent());   
        $server2 = array(
            'Accept' => 'application/x.faeapp.v1+json', 
            'Fae-Client-Version' => 'ios-0.0.1',
            'Authorization' => 'FAE '.$array->debug_base64ed,
        );    
        $parameters = array(
            'geo_latitude' => 34.031960,
            'geo_longitude' => -118.288125,
        );
        $response = $this->call('post', 'http://'.$this->domain.'/map/user', $parameters, [], [], $this->transformHeadersToServerVars($server2));   
        $array2 = json_decode($response->getContent()); 
        $result = false;
        if ($response->status() == '422' && $array2->message == 'current user is not active') {
            $result = true;
        }
        $this->assertEquals(true, $result);
    }
}
