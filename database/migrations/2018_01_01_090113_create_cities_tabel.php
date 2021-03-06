<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCitiesTabel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('cities', function (Blueprint $table) {
          $table->increments('id');
          $table->string('title_ar');
          $table->string('title_en');
          $table->integer('parent_id')->default(0);
          $table->integer('level');
          $table->integer('this_order');
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
        Schema::dropIfExists('cities');
    }
}
