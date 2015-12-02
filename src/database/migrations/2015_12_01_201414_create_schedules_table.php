<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSchedulesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('schedules', function (Blueprint $table) {

            $table->increments('id');
            $table->string('command');
            $table->string('expression');
            $table->boolean('allow_overlap')->default(false);
            $table->boolean('allow_maintenance')->default(false);
            $table->string('ping_before')->nullable();
            $table->string('ping_after')->nullable();

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

        Schema::drop('schedules');
    }
}
