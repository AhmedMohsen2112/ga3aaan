<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResturantBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resturant_branches', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('resturant_id')->unsigned();
            $table->foreign('resturant_id')->references('id')->on('resturantes');

            $table->integer('city_id')->unsigned();
            $table->foreign('city_id')->references('id')->on('cities');

            $table->integer('region_id')->unsigned();
            $table->foreign('region_id')->references('id')->on('cities');

            $table->decimal('delivery_cost',11,2);
            $table->boolean('active');

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
        Schema::dropIfExists('resturant_branches');
    }
}
