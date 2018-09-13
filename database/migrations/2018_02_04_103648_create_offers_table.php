<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->increments('id');
            $table->date('available_until');
            $table->integer('type');
            $table->text('image');
            $table->boolean('active');
            $table->integer('this_order');
            $table->integer('discount');
            $table->string('menu_section_ids')->nullable();

            $table->integer('resturant_id')->unsigned();
            $table->foreign('resturant_id')->references('id')->on('resturantes');


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
        Schema::dropIfExists('offers');
    }
}
