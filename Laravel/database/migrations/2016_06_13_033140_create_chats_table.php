<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chats', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('user_a_id');
            $table->foreign('user_a_id')->references('id')->on('users');
            $table->integer('user_b_id');
            $table->foreign('user_b_id')->references('id')->on('users');
            $table->integer('last_message_sender_id');
            $table->foreign('last_message_sender_id')->references('id')->on('users');
            $table->text('last_message');
            $table->timestamp('last_message_timestamp');
            $table->enum('last_message_type',['text','image']);
            $table->integer('user_a_unread_count')->default(0);
            $table->integer('user_b_unread_count')->default(0);
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
        Schema::drop('chats');
    }
}
