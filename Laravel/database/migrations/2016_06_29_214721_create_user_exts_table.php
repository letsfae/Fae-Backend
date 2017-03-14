<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserExtsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_exts', function (Blueprint $table) {
            $table->integer('user_id');
            $table->integer('status')->default('0');
            $table->string('message',100)->nullable();
            $table->boolean('show_user_name')->default(true);
            $table->boolean('show_email')->default(true);
            $table->boolean('show_phone')->default(true);
            $table->boolean('show_gender')->default(true);
            $table->boolean('show_birthday')->default(true);
            $table->primary('user_id');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_exts');
    }
}
