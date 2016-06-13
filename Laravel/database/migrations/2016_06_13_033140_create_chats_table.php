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
            $table->integer('from_user')->unsigned();
            $table->foreign('from_user')->references('id')->on('users');
            $table->integer('to_user')->unsigned();
            $table->foreign('to_user')->references('id')->on('users');
            $table->string('last_message');
            $table->integer('unread_count')->default(0);
            $table->string('firebase_id',30);
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
