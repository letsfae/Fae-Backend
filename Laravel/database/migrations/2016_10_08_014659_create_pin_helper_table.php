<?php

use Phaza\LaravelPostgis\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePinHelperTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pin_helper', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type',['media','comment','chat_room']);
            $table->integer('pin_id')->unsigned();
            $table->point('geolocation')->nullable();
            $table->integer('duration')->unsigned();
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
        Schema::drop('pin_helper');
    }
}
