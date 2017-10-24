<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email',50);
            $table->boolean('email_verified')->default(false);
            $table->string('password',70);
            $table->integer('login_count')->default(0);
            $table->string('user_name',30);
            $table->string('first_name',20)->nullable();
            $table->string('last_name',20)->nullable();
            $table->string('phone',20)->nullable();
            $table->boolean('phone_verified')->default(false);
            $table->enum('gender',['male','female'])->nullable();
            $table->date('birthday')->nullable();
            $table->integer('role')->default(0);
            $table->integer('mini_avatar')->default(0);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->unique('user_name');
            $table->unique('email');
            $table->unique('phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
