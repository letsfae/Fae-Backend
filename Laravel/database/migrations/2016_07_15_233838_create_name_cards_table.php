<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNameCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('name_cards', function (Blueprint $table) {
            $table->integer('user_id');
            $table->string('nick_name',50)->nullable();
            $table->text('short_intro')->nullable();
            $table->text('tag_ids')->nullable();
            $table->boolean('show_gender')->default(false);
            $table->boolean('show_age')->default(false);
            $table->primary('user_id');
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::drop('name_cards');
    }
}
