<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderMealChoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_meal_choices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_meal_id')->unsigned();
            $table->integer('sub_choice_id')->unsigned();
            $table->decimal('price');
            $table->foreign('order_meal_id')->references('id')->on('order_meals');
            $table->foreign('sub_choice_id')->references('id')->on('sub_choices');
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
        Schema::dropIfExists('order_meal_choices');
    }
}
