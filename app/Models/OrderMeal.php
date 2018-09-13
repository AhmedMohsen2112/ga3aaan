<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;



class OrderMeal extends MyModel
{
    protected $table = "order_meals";
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function order_meal_toppings()
    {
    	return $this->hasMany(OrderMealTopping::class,'order_meal_id')->withTrashed();
    }
    public function order_meal_choices()
    {
    	return $this->hasMany(OrderMealChoice::class,'order_meal_id')->withTrashed();
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class,'meal_id');
    }

    public static function transform($item)
    {
        $titleSlug = 'title_'.static::getLangCode();
        $transformer = new \stdclass();
        $transformer->meal_title = $item->meal_title;
        $transformer->size_title = $item->size_title;
        $transformer->quantity =  $item->quantity;
        $transformer->price =  $item->cost_of_meal;

        return $transformer;
    }
    public static function transformForOrder($item)
    {
        $transformer = new \stdclass();
        $transformer->meal_title = $item->meal_title;
        $transformer->size_title = $item->size_title;
        $transformer->quantity =  $item->quantity;
        $transformer->price =  $item->cost_of_meal;

        return $transformer;
    }
    public static function transformEditOrder($item)
    {
        $titleSlug = 'title_'.static::getLangCode();
        $transformer = new \stdclass();
        $transformer->id = $item->id;
        $transformer->meal_title = $item->meal_title;
        $transformer->size_title = $item->size_title;
        $transformer->quantity =  $item->quantity;
        $transformer->total_price =  $item->cost_of_quantity;
        $transformer->toppings=static::getMealToppings($item->id);

        return $transformer;
    }
    
    private static function getMealToppings($order_meal_id) {
        $titleSlug = 'title_'.static::getLangCode();
        $Toppings = OrderMeal::join('order_meal_toppings', 'order_meals.id', '=', 'order_meal_toppings.order_meal_id');
        $Toppings->join('meal_toppings', 'meal_toppings.id', '=', 'order_meal_toppings.meal_topping_id');
        $Toppings->join('menu_section_toppings', 'menu_section_toppings.id', '=', 'meal_toppings.menu_section_topping_id');
        $Toppings->join('toppings', 'toppings.id', '=', 'menu_section_toppings.topping_id');
        $Toppings->select('order_meal_toppings.*', "toppings.$titleSlug as title");
        $Toppings->where('order_meals.id', $order_meal_id);
        $result = $Toppings->get();
        return $result;
    }

     protected static function boot() {
        parent::boot();

        static::deleting(function($order_meal) {
            if ($order_meal->forceDeleting) {
                foreach ($order_meal->order_meal_choices as $item) {
                  $item->forceDelete();
                }
            }
            else{
                foreach ($order_meal->order_meal_choices as $item) {
                   $item->delete();
                }
            }
        	
        });
    }
}
