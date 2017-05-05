<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Name_cards;
use App\User_exts;

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
        $user_exts = new User_exts;
        $user_exts->user_id = 1;
        $user_exts->save();
        $nameCard = new Name_cards;
        $nameCard->user_id = 1;
        $nameCard->save();
        for($i = 1; $i < 100; $i++)
        {
        	DB::table('users')->insert([
            	'email' => 'fae'.$i.'@gmail.com',
            	'password' => bcrypt('faelalala'),
            	'user_name' => 'fae'.$i,
        	]);
            $user_exts = new User_exts;
            $user_exts->user_id = $i + 1;
            $user_exts->save();
            $nameCard = new Name_cards;
            $nameCard->user_id = $i + 1;
            $nameCard->save();
        }
    }
}
