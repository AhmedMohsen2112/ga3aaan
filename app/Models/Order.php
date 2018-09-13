<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Order extends MyModel {

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    /* public function getCreatedAtAttribute($attr) {
      return Carbon::parse($attr)->format('Y/m/d');
      } */

    public function order_meals() {
        return $this->hasMany(OrderMeal::class, 'order_id')->withTrashed();
    }

    public function address() {
        return $this->belongsTo(Address::class, 'user_address_id')->withTrashed();
    }

    public function resturant() {
        return $this->belongsTo(Resturant::class);
    }

    public static function transform($item) {
        $transformer = new \stdClass();
        $transformer->id = $item->id;
        $transformer->resturant = $item->resturant;
        $transformer->region = $item->region;
        $transformer->user_address = Address::transform($item->address);
        $transformer->toppings_price = $item->toppings_price;
        $transformer->service_charge = $item->service_charge;
        $transformer->vat = $item->vat;
        $transformer->delivery_cost = $item->delivery_cost;
        $transformer->primary_price = $item->primary_price;
        $transformer->total_cost = $item->total_cost;
        $transformer->net_cost = $item->net_cost;
        if ($item->coupon) {
            $transformer->coupon = $item->coupon;
        } else {
            $transformer->coupon = "";
        }
        $order_meals = static::getOrderMeals($item->id);
        $new_order_meals = [];
        foreach ($order_meals as $order_meal) {
            $meal = OrderMeal::transformForOrder($order_meal);
            $choices = static::getOrderMealChoices($order_meal->id);
            $meal->sub_choices = [];
            if ($choices->count() > 0) {
                foreach ($choices as $choice) {
                    $meal->sub_choices[] = SubChoice::transformForOrder($choice);
                }
            }
            array_push($new_order_meals, $meal);
        }
        $transformer->meals = $new_order_meals;
        $transformer->status = $item->status;
        $transformer->status_text = _lang('app.' . static::$status_text[$item->status]);
        $transformer->resturant_image = url('public/uploads/resturantes/' . $item->image);
        $transformer->resturant_id = $item->resturant_id;
        $transformer->is_rated = $item->is_rated;

        $transformer->date = date('Y/m/d', strtotime($item->date));





        return $transformer;
    }

    public static function transformForPagination($item) {
        $transformer = new \stdClass();
        $transformer->id = $item->id;
        $transformer->resturant = $item->resturant;
        $transformer->region = $item->region;
        $transformer->resturant_image = url('public/uploads/resturantes/' . $item->image);
        $transformer->status = $item->status;

        $transformer->status_text = _lang('app.' . static::$status_text[$item->status]);

        $transformer->resturant_slug = $item->resturant_slug;
        $transformer->date = $item->created_at;

        return $transformer;
    }

    public static function getOrderMeals($order_id) {
        $lang_code = static::getLangCode();
        $order_meals = OrderMeal::withTrashed()->where('order_id', $order_id)
                ->join('meals', 'order_meals.meal_id', '=', 'meals.id')
                ->join('menu_sections', 'meals.menu_section_id', '=', 'menu_sections.id')
                ->leftJoin('meal_sizes', 'order_meals.meal_size_id', '=', 'meal_sizes.id')
                ->leftJoin('sizes', 'meal_sizes.size_id', '=', 'sizes.id')
                ->select(['order_meals.id', 'order_meals.meal_id_in_cart', 'order_meals.comment', 'order_meals.meal_id', 'order_meals.meal_size_id', 'order_meals.quantity',
                    'order_meals.cost_of_meal', 'order_meals.cost_of_quantity', "order_meals.sub_choices_price", 'meals.price as meal_price', 'meal_sizes.price as size_price',
                    'menu_sections.id as menu_section_id', 'meals.title_' . $lang_code . ' as meal_title', 'sizes.title_' . $lang_code . ' as size_title',
                   'sizes.title_ar as size_title_ar', 'sizes.title_en as size_title_en','meals.title_ar as meal_title_ar', 'meals.title_en as meal_title_en'])
                ->get();
        return $order_meals;
    }

    public static function getOrderMealChoices($order_meal_id) {
        $lang_code = static::getLangCode();
        $choices = OrderMeal::withTrashed()->join('order_meal_choices', 'order_meals.id', '=', 'order_meal_choices.order_meal_id')
                ->join('sub_choices', 'sub_choices.id', '=', 'order_meal_choices.sub_choice_id')
                ->where('order_meals.id', $order_meal_id)
                ->select([
                    "sub_choices.id", "sub_choices.title_$lang_code as title", "order_meal_choices.price","sub_choices.price as sub_choice_price",
                    'sub_choices.title_ar as title_ar', 'sub_choices.title_en as title_en'
                ])
                ->get();


        return $choices;
    }

    protected static function boot() {
        parent::boot();

        static::deleting(function($order) {
            foreach ($order->order_meals as $item) {
                $item->delete();
            }
        });
    }

}
