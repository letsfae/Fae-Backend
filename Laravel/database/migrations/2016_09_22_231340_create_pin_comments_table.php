<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePinCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('pin_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type',['media','comment']);
            $table->boolean('anonymous')->default(false);
            $table->integer('pin_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('content',100)->nullable();
            $table->integer('vote_up_count')->default(0);
            $table->integer('vote_down_count')->default(0);
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
         Schema::drop('pin_comments');
    }
}
