<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('city');
            $table->string('region');
            $table->string('sub_region');
            $table->string('street');
            $table->string('building_number');
            $table->string('floor_number');
            $table->string('apartment_number');
            $table->text('special_sign');
            $table->text('extra_info');


           $table->integer('user_id')->unsigned();
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
        Schema::dropIfExists('addresses');
    }
}
