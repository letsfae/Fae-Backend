<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
         Schema::create('files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned(); 
            $table->foreign('user_id')->references('id')->on('users');
            $table->text('description')->nullable();
            $table->string('custom_tag',20)->nullable();
            $table->enum('type',['image','video']);
            $table->string('mine_type',30);
            $table->integer('size')->unsigned()->default(0);
            $table->string('hash',50);
            $table->string('directory',256);
            $table->string('file_name_storage',256);
            $table->string('file_name',256);
            $table->integer('reference_count')->unsigned();
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
        //
    }
}
