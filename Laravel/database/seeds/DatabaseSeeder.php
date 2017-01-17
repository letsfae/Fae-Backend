<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(Name_Card_Tags_TableSeeder::class);
        $this->call(UsersTableSeeder::class);
    }
}