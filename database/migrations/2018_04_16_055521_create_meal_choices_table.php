<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealChoicesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('meal_choices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('meal_id')->unsigned();
            $table->integer('meal_size_id')->nullable()->unsigned();
            $table->integer('choice_id')->unsigned();
            $table->integer('min');
            $table->integer('max');
            $table->foreign('meal_id')->references('id')->on('meals');
            $table->foreign('meal_size_id')->references('id')->on('meal_sizes');
            $table->foreign('choice_id')->references('id')->on('choices');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('meal_choices');
    }

}
