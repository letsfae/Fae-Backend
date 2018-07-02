<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->boolean('email_subscription')->default(true);
            $table->boolean('show_name_card_options')->default(true);
            $table->enum('measurement_units', ['imperial', 'metric'])->default('imperial');
            $table->enum('shadow_location_system_effect', ['min', 'normal','max'])->nullable();
            $table->string('others',70)->nullable();
            $table->primary('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_settings');
    }
}
