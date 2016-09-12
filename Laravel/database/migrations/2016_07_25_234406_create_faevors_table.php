<?php

use Illuminate\Database\Migrations\Migration;
use Phaza\LaravelPostgis\Schema\Blueprint;

class CreateFaevorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('faevors', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->text('description');
            $table->point('geolocation');
            $table->string('name',100);
            $table->integer('budget');
            $table->string('bonus',50)->nullable();
            $table->dateTime('expire_time');
            $table->dateTime('due_time');
            $table->text('tag_ids')->nullable();
            $table->text('file_ids')->nullable();
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
        Schema::drop('faevors');
    }
}
