<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResturantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resturantes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title_ar');
            $table->string('title_en');
            $table->boolean('active');
            $table->integer('options')->default(0);
            /*
             1 => new 
             2 => ad
            */
            $table->text('image');
            $table->integer('delivery_time');
            $table->integer('minimum_charge');
            $table->text('working_hours');
            $table->decimal('service_charge',11,2);
            $table->decimal('vat',11,2);
            $table->float('rate')->default(0);
            $table->decimal('commission',11,2);
            $table->integer('views')->default(0);
            $table->boolean('available')->default(true);
           
           $table->integer('category_id')->unsigned();
           $table->foreign('category_id')->references('id')->on('categories');

           $table->integer('admin_id')->unsigned();
           $table->foreign('admin_id')->references('id')->on('admins');

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
        Schema::dropIfExists('resturantes');
    }
}
