<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHashtaggableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('hashtaggables', function(Blueprint $table)
        {
            $table->integer('hashtags_id')->unsigned();
            $table->integer('hashtaggable_id')->unsigned();
            $table->string('hashtaggable_type', 20);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop('hashtaggables');
    }
}
