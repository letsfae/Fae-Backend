<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePinOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pin_operations', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type',['media','comment']);
            $table->integer('pin_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->boolean('liked')->default(false);
            $table->timestamp('liked_timestamp')->nullable();
            $table->boolean('saved')->default(false);
            $table->timestamp('saved_timestamp')->nullable();
            $table->integer('feeling')->nullable();
            $table->timestamp('feeling_timestamp')->nullable();
            $table->boolean('interacted')->default(false);
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
        Schema::drop('pin_operations');
    }
}
