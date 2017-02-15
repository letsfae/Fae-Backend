<?php

use Illuminate\Database\Migrations\Migration;
use Phaza\LaravelPostgis\Schema\Blueprint;

class CreateMediasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medias', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->text('description')->nullable();
            $table->point('geolocation');
            $table->text('tag_ids')->nullable();
            $table->text('file_ids');
            $table->integer('duration')->unsigned();
            $table->integer('interaction_radius')->unsigned()->default(0);
            $table->integer('saved_count')->default(0);
            $table->integer('liked_count')->default(0);
            $table->integer('comment_count')->default(0);
            $table->boolean('anonymous')->default(false);
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
        Schema::drop('medias');
    }
}
