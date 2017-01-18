<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_rooms', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('title',100);
            $table->point('geolocation');
            $table->integer('last_message_sender_id')->nullable();
            $table->foreign('last_message_sender_id')->references('id')->on('users');
            $table->text('last_message')->nullable();
            $table->timestamp('last_message_timestamp')->nullable();
            $table->enum('last_message_type',['text','image','sticker','location','audio'])->nullable();
            $table->integer('duration')->unsigned();
            $table->integer('interaction_radius')->unsigned()->default(0);
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
        Schema::drop('chat_rooms');
    }
}
