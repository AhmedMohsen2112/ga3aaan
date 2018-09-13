<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title_ar', 255);
            $table->string('title_en', 255);
            $table->string('address_ar', 255);
            $table->string('address_en', 255);
            $table->string('email');
            $table->integer('phone');
            $table->integer('fax');
            $table->longText('about_us_ar');
            $table->longText('about_us_en');
            $table->longText('usage_conditions_ar');
            $table->longText('usage_conditions_en');
            $table->text('social_media');
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
        Schema::dropIfExists('settings');
    }
}
