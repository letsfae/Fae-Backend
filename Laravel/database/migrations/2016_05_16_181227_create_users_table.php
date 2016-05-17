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
            $table->increments('user_id');
            $table->string('email',50);
            $table->string('passwaord',70);
            $table->integer('login_count')->default(0);
            $table->string('username',30)->nullable();
            $table->string('first_name',20)->nullable();
            $table->string('last_name',20)->nullable();
            $table->enum('gender',['male','female'])->nullable();
            $table->dateTime('birth_day')->nullable();
            $table->integer('role')->nullable();
            $table->text('address')->nullable();
            $table->string('cell_phone',20)->nullable();
            $table->timestamps();
            $table->unique('username');
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
