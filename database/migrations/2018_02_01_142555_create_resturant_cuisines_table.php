<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResturantCuisinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resturant_cuisines', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('resturant_id')->unsigned();
            $table->foreign('resturant_id')->references('id')->on('resturantes');

            $table->integer('cuisine_id')->unsigned();
            $table->foreign('cuisine_id')->references('id')->on('cuisines');

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
        Schema::dropIfExists('resturant_cuisines');
    }
}
