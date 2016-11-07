<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUseratableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('useratables', function (Blueprint $table) {
            $table->integer('users_id')->unsigned();
            $table->integer('useratable_id')->unsigned();
            $table->string('useratable_type', 20);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('useratables');
    }
}
