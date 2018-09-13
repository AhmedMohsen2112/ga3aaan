<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class OrderMealTopping extends MyModel {

    protected $table = "order_meal_toppings";

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public static function transform($item) {
        $obj=new \stdClass();
        $obj->id=$item->id;
        $obj->title_ar=$item->title_ar;
        $obj->title_en=$item->title_en;
        $obj->price=$item->price;
        $obj->quantity=$item->quantity;
        
        return $obj;
    }

}
