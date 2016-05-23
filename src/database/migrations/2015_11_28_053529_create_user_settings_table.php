<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

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

            $table->increments('id');
            $table->integer('user_id');
            $table->string('name');
            $table->string('value');
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('name');
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
