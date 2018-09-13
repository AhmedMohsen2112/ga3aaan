<?php

namespace App\Models;

use DB;

class Meal extends MyModel {

    protected $table = "meals";
    public static $sizes = array(
        's' => array('width' => 200, 'height' => 200),
        'm' => array('width' => 600, 'height' => 400),
        'l' => array('width' => 900, 'height' => 400),
    );

    public function sizes() {
        return $this->belongsToMany(Size::class, 'meal_sizes', 'meal_id', 'size_id')->withPivot('id', 'price');
    }

    public function menu_section() {
        return $this->belongsTo(MenuSection::class);
    }

    public function favourites() {
        return $this->belongsToMany(User::class, 'favourites', 'meal_id', 'user_id');
    }

    public function choices() {
        return $this->belongsToMany(Choice::class, 'meal_choices', 'meal_id', 'choice_id')
                        ->select('meal_choices.id as meal_choice_id', 'meal_choices.choice_id', "meal_choices.min", "meal_choices.max");
    }

    public function toppings() {
        return $this->belongsToMany(MenuSectionTopping::class, 'meal_toppings', 'meal_id', 'menu_section_topping_id')->withPivot('id');
    }

    protected static function transformCollection($items, $type = null) {

        $transformers = array();

        if ($type == null) {
            $transform = 'transform';
        } else {
            $transform = 'transform' . $type;
        }
        if (count($items)) {

            $discount = static::getDiscount($items[0]);

            $user = static::auth_user();

            foreach ($items as $item) {

                $transformers[] = self::$transform($item, $discount, $user);
            }
        }

        return $transformers;
    }

    public static function transform($item, $user) {
        $discount = static::getDiscount($item);
        $titleSlug = 'title_' . static::getLangCode();
        $descriptionSlug = 'description_' . static::getLangCode();

        $transformer = new \stdClass();
        $transformer->id = $item->id;
        $transformer->title = $item->$titleSlug;
        $transformer->description = $item->$descriptionSlug;


        $transformer->price = $item->price;
        if ($discount != 0) {
            $transformer->discount_price = $item->price - (($item->price * $discount) / 100);
        } else {
            $transformer->discount_price = 0;
        }

        $transformer->image = url('public/uploads/meals') . '/' . $item->image;

        if ($item->sizes) {
            $counter = 0;
            $sizes = array();
            if ($discount != 0) {
                $transformer->sizes = $item->sizes()->select('meal_sizes.id', 'meal_sizes.price', 'sizes.' . $titleSlug . ' as size', DB::raw('( meal_sizes.price - ( (meal_sizes.price * ' . $discount . ') / 100 ) ) as discount_price')
                        )->get();
            } else {
                $transformer->sizes = $item->sizes()->select('meal_sizes.id', 'meal_sizes.price', 'sizes.' . $titleSlug . ' as size')->get();
            }
        }

        if ($item->toppings) {
            $transformer->toppings = $item->toppings()->join('toppings', 'menu_section_toppings.topping_id', '=', 'toppings.id')->select('meal_toppings.id', 'menu_section_toppings.price', 'toppings.' . $titleSlug . ' as topping')->get()->toArray();
        } else {
            $transformer->toppings = array();
        }


        if ($item->is_favourite != null) {
            $transformer->is_favourite = 1;
        } else {
            $transformer->is_favourite = 0;
        }
        $transformer->service_charge = $item->service_charge;
        $transformer->vat = $item->vat;
        $transformer->delivery_cost = $item->delivery_cost;
        $transformer->payment_methods = PaymentMethod::transformCollection($item->menu_section->resturant->payment_methods);
        return $transformer;
    }

    public static function transformForPagination($item) {
        $discount = static::getDiscount($item);
        $titleSlug = 'title_' . static::getLangCode();
        $descriptionSlug = 'description_' . static::getLangCode();

        $transformer = new \stdClass();
        $transformer->id = $item->id;
        $transformer->title = $item->$titleSlug;
        $transformer->description = $item->$descriptionSlug;
        $transformer->slug = $item->slug;
        $transformer->resturant_slug = $item->resturant_slug;
        //$transformer->resturant_slug = $item->branch_slug ? $item->title . ' - ' . $item->branch_title : $item->title;
        $transformer->menu_section_slug = $item->menu_section_slug;


        $transformer->price = $item->price;
        if ($discount != 0) {
            $transformer->discount_price = $item->price - (($item->price * $discount) / 100);
        } else {
            $transformer->discount_price = 0;
        }

        $transformer->image = url('public/uploads/meals') . '/' . $item->image;

        $transformer->is_favourite = $item->favourite_id != null ? true : false;
        $transformer->is_open = self::checkIsOpen(json_decode($item->working_hours)) == true ? 1 : 0;
        $transformer->config = array(
            'meal_id' => $item->id, 'resturant_id' => $item->resturant_id, 'menu_section_id' => $item->menu_section_id, 'is_open' => $transformer->is_open,
            'toppings' => $item->toppings, 'service_charge' => $item->service_charge, 'resturant_slug' => $transformer->resturant_slug,
            'vat' => $item->vat, 'delivery_cost' => $item->delivery_cost, 'resturant_branch_id' => $item->resturant_branch_id
        );

        return $transformer;
    }

    public static function transformForDetails($item) {

        $discount = static::getDiscount($item);
        $titleSlug = 'title_' . static::getLangCode();
        $descriptionSlug = 'description_' . static::getLangCode();

        $transformer = new \stdClass();
        $transformer->id = $item->id;
        $transformer->title = $item->$titleSlug;
        $transformer->description = $item->$descriptionSlug;
        $transformer->resturant_id = $item->resturant_id;
        $transformer->resturant_branch_id = $item->resturant_branch_id;
        $transformer->slug = $item->slug;
        $transformer->has_sizes = $item->has_sizes;
        $transformer->resturant_slug = $item->resturant_slug;
        $transformer->menu_section_slug = $item->menu_section_slug;


        $transformer->price = $item->price;
        if ($discount != 0) {
            $transformer->discount_price = $item->price - (($item->price * $discount) / 100);
        } else {
            $transformer->discount_price = 0;
        }


        $transformer->image = url('public/uploads/meals') . '/m_' . static::rmv_prefix($item->image);

        $transformer->is_favourite = $item->favorite_id != null ? true : false;
        $transformer->is_open = static::checkIsOpen(json_decode($item->working_hours)) == true ? 1 : 0;
//        $transformer->sizes = $item->sizes()->select('meal_sizes.id', 'meal_sizes.price', 'sizes.' . $titleSlug . ' as size', DB::raw("ROUND(( meal_sizes.price - ( (meal_sizes.price * $discount) / 100 ) ), 2)as discount_price"))->get();
        $transformer->sizes = $item->sizes()->select('meal_sizes.id', 'meal_sizes.price', 'sizes.' . $titleSlug . ' as size', DB::raw("CASE WHEN $discount > 0 THEN ROUND(( meal_sizes.price - ( (meal_sizes.price * $discount) / 100 ) ), 2) ELSE 0 END  as discount_price"))->get();
        $transformer->config = array(
            'meal_id' => $item->id, 'resturant_id' => $item->resturant_id, 'menu_section_id' => $item->menu_section_id, 'is_open' => $transformer->is_open,
            'service_charge' => $item->service_charge, 'resturant_slug' => $transformer->resturant_slug,
            'vat' => $item->vat, 'has_sizes' => $item->has_sizes, 'delivery_cost' => $item->delivery_cost, 'resturant_branch_id' => $item->resturant_branch_id
        );


        return $transformer;
    }

    public static function transformForChoices($item) {

        $discount = static::getDiscount($item);
        $titleSlug = 'title_' . static::getLangCode();
        $descriptionSlug = 'description_' . static::getLangCode();

        $transformer = new \stdClass();
        $transformer->id = $item->id;
        $transformer->title = $item->$titleSlug;
        $transformer->description = $item->$descriptionSlug;
        $transformer->price = $item->price;
        if ($discount != 0) {
            $transformer->discount_price = $item->price - (($item->price * $discount) / 100);
        } else {
            $transformer->discount_price = 0;
        }


        $transformer->image = url('public/uploads/meals') . '/m_' . static::rmv_prefix($item->image);




        return $transformer;
    }

    public static function transformMenu_section($item, $discount, $user) {
        $titleSlug = 'title_' . static::getLangCode();

        $transformer = new \stdClass();
        $transformer->id = $item->id;
        $transformer->title = $item->$titleSlug;
        $transformer->price = $item->price;
        if ($discount != 0) {
            $transformer->discount_price = $item->price - (($item->price * $discount) / 100);
        }
        $transformer->image = url('public/uploads/meals') . '/m_' . static::rmv_prefix($item->image);
        if ($item->is_favourite != null) {
            $transformer->is_favourite = 1;
        } else {
            $transformer->is_favourite = 0;
        }
        return $transformer;
    }

    public static function getDiscount($meal) {
        $offer = $meal->menu_section->resturant->offer;
        $discount = 0;
        if ($offer) {
            if ($offer->type == 1) {
                $discount = $offer->discount;
            } elseif ($offer->type == 2) {
                $menu_sections = explode(",", $offer->menu_section_ids);
                if (!in_array($meal->menu_section->id, $menu_sections)) {
                    $discount = $offer->discount;
                }
            } elseif ($offer->type == 3) {
                $menu_sections = explode(",", $offer->menu_section_ids);
                if (in_array($meal->menu_section->id, $menu_sections)) {
                    $discount = $offer->discount;
                }
            }
        }
        return $discount;
    }

    public static function getMealSizeChoices($where_arr, $transform = 'front') {
        $lang_code = static::getLangCode();
        $meal_choices = MealChoice::join('meal_sub_choices', 'meal_choices.id', '=', 'meal_sub_choices.meal_choice_id');
        $meal_choices->join('choices', 'choices.id', '=', 'meal_choices.choice_id');
        $meal_choices->join('sub_choices', 'sub_choices.id', '=', 'meal_sub_choices.sub_choice_id');
        if (isset($where_arr['meal_id'])) {
            $meal_choices->where('meal_choices.meal_id', $where_arr['meal_id']);
        }
        if (isset($where_arr['meal_size_id'])) {
            $meal_choices->where('meal_choices.meal_size_id', $where_arr['meal_size_id']);
        }

        $meal_choices->select(['meal_choices.id', 'meal_sub_choices.sub_choice_id',
            "choices.title_$lang_code as choice_title", "sub_choices.title_$lang_code as sub_choice_title", "sub_choices.price as sub_choice_price",
            "meal_choices.min", "meal_choices.max"]);
        $meal_choices = $meal_choices->get();
        //dd($request->all());
        $result = [];
        if ($meal_choices->count() > 0) {
            foreach ($meal_choices as $one) {
                if (isset($result[$one->id])) {
                    $sub_choice = new \stdClass();
                    $sub_choice->id = $one->sub_choice_id;
                    $sub_choice->title = $one->sub_choice_title;
                    $sub_choice->price = $one->sub_choice_price;
                    $result[$one->id]->sub[] = $sub_choice;
                } else {
                    $choice = new \stdClass();
                    $choice->id = $one->id;
                    $choice->title = $one->choice_title;
                    $choice->min = $one->min;
                    $choice->max = $one->max;
                    $sub_choice = new \stdClass();
                    $sub_choice->id = $one->sub_choice_id;
                    $sub_choice->title = $one->sub_choice_title;
                    $sub_choice->price = $one->sub_choice_price;
                    $choice->sub = [];
                    $choice->sub[] = $sub_choice;
                    $result[$choice->id] = $choice;
                }
            }
        }
        $result = $transform == 'front' ? $result : array_values($result);
        return $result;
    }

    public static function getMealSizeChoices2($where_arr) {
        $lang_code = static::getLangCode();
        $meal_choices = MealChoice::join('meal_sub_choices', 'meal_choices.id', '=', 'meal_sub_choices.meal_choice_id');
        $meal_choices->join('choices', 'choices.id', '=', 'meal_choices.choice_id');
        $meal_choices->join('sub_choices', 'sub_choices.id', '=', 'meal_sub_choices.sub_choice_id');
        if (isset($where_arr['meal_id'])) {
            $meal_choices->where('meal_choices.meal_id', $where_arr['meal_id']);
        }
        if (isset($where_arr['meal_size_id'])) {
            $meal_choices->where('meal_choices.meal_size_id', $where_arr['meal_size_id']);
        }

        $meal_choices->select(['meal_choices.id', 'meal_sub_choices.sub_choice_id',
            "choices.title_$lang_code as choice_title", "sub_choices.title_$lang_code as sub_choice_title", "sub_choices.price as sub_choice_price",
            "meal_choices.min", "meal_choices.max"]);
        $meal_choices = $meal_choices->get();
        //dd($request->all());
        $result = [];
        if ($meal_choices->count() > 0) {
            foreach ($meal_choices as $one) {
                if (isset($result[$one->id])) {
                    $sub_choice = new \stdClass();
                    $sub_choice->id = $one->sub_choice_id;
                    $sub_choice->title = $one->sub_choice_title;
                    $sub_choice->price = $one->sub_choice_price;
                    $result[$one->id]->sub[] = $sub_choice;
                } else {
                    $choice = new \stdClass();
                    $choice->id = $one->id;
                    $choice->title = $one->choice_title;
                    $choice->min = $one->min;
                    $choice->max = $one->max;
                    $sub_choice = new \stdClass();
                    $sub_choice->id = $one->sub_choice_id;
                    $sub_choice->title = $one->sub_choice_title;
                    $sub_choice->price = $one->sub_choice_price;
                    $choice->sub = [];
                    $choice->sub[] = $sub_choice;
                    $result[$choice->id] = $choice;
                }
            }
        }
        return $result;
    }

    protected static function boot() {
        parent::boot();

        static::deleting(function($meal) {
            //$meal->sizes()->detach();
        });
    }

}
