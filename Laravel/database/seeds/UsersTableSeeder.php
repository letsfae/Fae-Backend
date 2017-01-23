<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
                'email' => 'faemapcrew@gmail.com',
                'password' => bcrypt('faelalala'),
                'user_name' => 'Fae Map Crew',
            ]);
        for($i = 2; $i < 100; $i++)
        {
        	DB::table('users')->insert([
            	'email' => 'fae'.$i.'@gmail.com',
            	'password' => bcrypt('faelalala'),
            	'user_name' => 'fae'.$i,
        	]);
        }
    }
}
