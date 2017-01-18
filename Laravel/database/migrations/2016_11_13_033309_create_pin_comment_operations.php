<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePinCommentOperations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pin_comment_operations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pin_comment_id')->unsigned();
            $table->foreign('pin_comment_id')->references('id')->on('pin_comments');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('vote');
            $table->timestamp('vote_timestamp');
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
        Schema::drop('pin_comment_operations');
    }
}
