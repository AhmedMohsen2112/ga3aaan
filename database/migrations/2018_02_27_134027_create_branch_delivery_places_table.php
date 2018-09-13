<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBranchDeliveryPlacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branch_delivery_places', function (Blueprint $table) {
           
            $table->increments('id');

            $table->integer('resturant_branch_id')->unsigned();
            $table->foreign('resturant_branch_id')->references('id')->on('resturant_branches');

            $table->integer('region_id')->unsigned();
            $table->foreign('region_id')->references('id')->on('cities');

            $table->decimal('delivery_cost');

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
        Schema::dropIfExists('branch_delivery_places');
    }
}
