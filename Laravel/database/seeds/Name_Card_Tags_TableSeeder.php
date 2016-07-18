<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class Name_Card_Tags_TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('name_card_tags')->insert([
        	[
            	'title' => 'hmu',
            	'color' => '#B2E44D',
        	],
        	[
            	'title' => 'i do faevors',
            	'color' => '#FF7E7E',
        	],
        	[
            	'title' => 'seller',
            	'color' => '#48DDBC',
        	],
        	[
            	'title' => 'service',
            	'color' => '#97A1EC',
        	],
        	[
            	'title' => 'If friends',
            	'color' => '#E68C66',
        	],
        	[
            	'title' => 'students',
            	'color' => '#91D1FC',
        	],
        	[
            	'title' => 'foodie',
            	'color' => '#FBCA51',
        	],
        	[
            	'title' => 'for hire',
            	'color' => '#E46C6C',
        	],
        	[
            	'title' => 'dating',
            	'color' => '#DF9DF8',
        	],
        	[
            	'title' => 'local',
            	'color' => '#7AD486',
        	],
        	[
            	'title' => 'visitor',
            	'color' => '#C09AD2',
        	],
        	[
            	'title' => 'AMA',
            	'color' => '#FCBF9D',
        	],
        	[
            	'title' => 'athlete',
            	'color' => '#8A8A8A',
        	],
        	[
            	'title' => 'traveller',
            	'color' => '#66A0E4',
        	],
        	[
            	'title' => 'bookworm',
            	'color' => '#A88178',
        	],
        	[
            	'title' => 'cinephile',
            	'color' => '#74B8BC',
        	],
        ]);
    }
}
