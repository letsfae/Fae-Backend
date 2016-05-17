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
            $table->string('password',70);
            $table->integer('login_count')->default(0);
            $table->string('user_name',30)->nullable();
            $table->string('first_name',20)->nullable();
            $table->string('last_name',20)->nullable();
            $table->enum('gender',['male','female'])->nullable();
            $table->dateTime('birthday')->nullable();
            $table->integer('role')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
            $table->unique('user_name');
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
