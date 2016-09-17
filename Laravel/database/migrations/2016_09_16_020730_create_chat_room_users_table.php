<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatRoomUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_room_users', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('chat_room_id');
            $table->foreign('chat_room_id')->references('id')->on('chat_rooms');
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('unread_count')->default(0);
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
        Schema::drop('chat_room_users');
    }
}
