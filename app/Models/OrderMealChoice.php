<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class OrderMealChoice extends MyModel {

    use SoftDeletes;

    protected $table = "order_meal_choices";

}
