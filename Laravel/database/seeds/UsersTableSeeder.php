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
        for($i = 0; $i < 99; $i++)
        {
        	DB::table('users')->insert([
            	'email' => 'fae'.$i.'@gmail.com',
            	'password' => bcrypt('faelalala'),
            	'user_name' => 'fae'.$i,
        	]);
        }
    }
}
