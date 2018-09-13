<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealSubChoicesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('meal_sub_choices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('meal_choice_id')->unsigned();
            $table->integer('sub_choice_id')->unsigned();
            $table->foreign('meal_choice_id')->references('id')->on('meal_choices');
            $table->foreign('sub_choice_id')->references('id')->on('sub_choices');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('meal_sub_choices');
    }

}
