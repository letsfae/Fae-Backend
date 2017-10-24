<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->text('content');
            $table->point('geolocation');
            $table->integer('saved_count')->default(0);
            $table->integer('liked_count')->default(0);
            $table->integer('comment_count')->default(0);
            $table->string('feeling_count')->default('0,0,0,0,0,0,0,0,0,0,0');
            $table->text('file_ids')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('locations');
    }
}
