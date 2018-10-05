<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterHistoricalPricesColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('historical_prices', function (Blueprint $table) {

            $table->decimal('average_price', 30, 2)->default(0.0)->change();
            $table->decimal('adjusted_price', 30, 2)->default(0.0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('historical_prices', function (Blueprint $table) {

            $table->decimal('average_price')->change();
            $table->decimal('adjusted_price')->change();
        });
    }
}
