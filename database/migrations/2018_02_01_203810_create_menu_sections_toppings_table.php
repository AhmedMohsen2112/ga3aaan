<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuSectionsToppingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_section_toppings', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('menu_section_id')->unsigned();
            $table->foreign('menu_section_id')->references('id')->on('menu_sections');

            $table->integer('topping_id')->unsigned();
            $table->foreign('topping_id')->references('id')->on('toppings');
            
            $table->decimal('price',11,2);

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
        Schema::dropIfExists('menu_sections_toppings');
    }
}
