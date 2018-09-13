<?php

namespace App\Models;

use DateTime;

class Resturant extends MyModel {

    protected $table = "resturantes";
    public static $sizes = array(
        's' => array('width' => 110, 'height' => 110)
    );
    public static $week_days = [
        ['title' => 'saturday', 'name' => 'Sat'], ['title' => 'sunday', 'name' => 'Sun'], ['title' => 'monday', 'name' => 'Mon'],
        ['title' => 'tuesday', 'name' => 'Tue'], ['title' => 'wednesday', 'name' => 'Wed'], ['title' => 'thursday', 'name' => 'Thu'],
        ['title' => 'friday', 'name' => 'Fri']
    ];

    public static function getAll($where_array, $transform_type = "transformOnePagination") {
        $lang_code = static::getLangCode();
        $resturantes = Resturant::join('resturant_branches', 'resturantes.id', '=', 'resturant_branches.resturant_id');

        $resturantes->join('branch_delivery_places', 'resturant_branches.id', '=', 'branch_delivery_places.resturant_branch_id');
        $resturantes->join('categories', 'categories.id', '=', 'resturantes.category_id');
        /* if (isset($where_array['city_id'])) {
          $resturantes->where('resturant_branches.city_id', $where_array['city_id']);
          } */
        if (isset($where_array['filter'])) {
            if (isset($where_array['filter']['has_offer'])) {
                $resturantes->join('offers', 'offers.resturant_id', '=', 'resturantes.id');
            }
            if (isset($where_array['filter']['cuisines'])) {
                $resturantes->join('resturant_cuisines', 'resturant_cuisines.resturant_id', '=', 'resturantes.id');
                $resturantes->join('cuisines', 'cuisines.id', '=', 'resturant_cuisines.cuisine_id');
            }
        }
        $resturantes->where('resturantes.available', 1);
        $resturantes->where('resturantes.active', 1);
        $resturantes->where('resturant_branches.active', 1);
        if (isset($where_array['region_id'])) {
            $resturantes->where('branch_delivery_places.region_id', $where_array['region_id']);
        }
        if (isset($where_array['category_id'])) {
            $resturantes->where('resturantes.category_id', $where_array['category_id']);
        }
        if (isset($where_array['resturant_slug'])) {
            $resturantes->where('resturantes.slug', $where_array['resturant_slug']);
        }
        if (isset($where_array['resturant_branch_slug'])) {
            $resturantes->where('resturant_branches.slug', $where_array['resturant_branch_slug']);
        }
        if (isset($where_array['cuisine_slug'])) {
            $resturantes->join('resturant_cuisines', 'resturantes.id', '=', 'resturant_cuisines.resturant_id');
            $resturantes->join('cuisines', 'cuisines.id', '=', 'resturant_cuisines.cuisine_id');
            $resturantes->where('cuisines.slug', $where_array['cuisine_slug']);
        }
        if (isset($where_array['query'])) {
            $resturantes->whereRaw(handleKeywordWhere(['resturantes.title_ar', 'resturantes.title_en'], $where_array['query']));
        }
        if (isset($where_array['filter'])) {
            if (isset($where_array['filter']['rating'])) {
                $resturantes->orderBy('resturant_branches.rate', 'DESC');
            }
            if (isset($where_array['filter']['has_offer'])) {
                $resturantes->where('offers.available_until', '>', date('Y-m-d'));
                $resturantes->where('offers.active', true);
            }
            if (isset($where_array['filter']['cuisines'])) {
                //dd($where_array['filter']['cuisines']);
                $resturantes->whereIn('cuisines.id', $where_array['filter']['cuisines']);
            }
            if (isset($where_array['filter']['cat'])) {
                $resturantes->where('categories.id', $where_array['filter']['cat']);
            }
            if (isset($where_array['filter']['q'])) {
                //dd($where_array['filter']['q']);
                $resturantes->whereRaw(handleKeywordWhere(['resturantes.title_ar', 'resturantes.title_en'], $where_array['filter']['q']));
            }
        }

        $resturantes->orderBy('branch_delivery_places.delivery_cost', 'ASC');
        $resturantes->select(["resturantes.*", "resturant_branches.rate", "resturant_branches.id as branch_id", "resturantes.title_$lang_code as title", "resturant_branches.title_$lang_code as branch_title", "resturant_branches.slug as branch_slug",
            'branch_delivery_places.delivery_cost', DB::raw("(SELECT Count(*) FROM offers WHERE resturant_id = resturantes.id and active = 1 and available_until > " . date('Y-m-d') . ") as offers")]);
        if ($transform_type == "transformOnePagination") {
            $resturantes = $resturantes->paginate(static::$limit);
            $resturantes->getCollection()->transform(function($resturant, $key) use($transform_type) {
                return Resturant::$transform_type($resturant);
            });
        } else {
            $resturantes = $resturantes->first();
            if ($resturantes) {
                $resturantes = Resturant::$transform_type($resturantes);
            }
        }


        return $resturantes;
    }

    public function payment_methods() {
        return $this->belongsToMany(PaymentMethod::class, 'resturant_payment_methods', 'resturant_id', 'payment_method_id');
    }

    public function cuisines() {
        return $this->belongsToMany(Cuisine::class, 'resturant_cuisines', 'resturant_id', 'cuisine_id');
    }

    public function branches() {
        return $this->hasMany(ResturantBranch::class);
    }

    public function active_menu_sections() {
        return $this->hasMany(MenuSection::class)->where('active', true)->orderBy('this_order');
    }

    public function offer() {
        return $this->hasOne(Offer::class)->where('active', true)->where('available_until', '>', date('Y-m-d'));
    }

    public function hasVisa() {
        return $this->payment_methods->contains(2);
    }

    public static function transform($item) {


        $titleSlug = "title_" . static::getLangCode();
        $resturant_branch = ResturantBranch::find($item->branch_id);

        $transformer = new \stdClass();
        $transformer->id = $item->id;
        $transformer->name = $item->{$titleSlug} . '-' . $item->region;
        $transformer->branch_id = $item->branch_id;
        $transformer->image = url('public/uploads/resturantes') . '/' . $item->image;
        $transformer->delivery_time = $item->delivery_time;
        $transformer->delivery_cost = ceil($item->delivery_cost);
        $transformer->minimum_charge = ceil($item->minimum_charge);
        $transformer->rate = $item->rate;

        if ($item->options == 1) {

            $transformer->is_new = 1;
            $transformer->is_ad = 0;
        } elseif ($item->options == 2) {

            $transformer->is_ad = 1;
            $transformer->is_new = 0;
        } else {
            $transformer->is_ad = 0;
            $transformer->is_new = 0;
        }
        $transformer->hasVisa = $item->hasVisa() == true ? 1 : 0;
        $transformer->has_offer = $item->has_offer;
        $transformer->working_hours = json_decode($item->working_hours);
        $transformer->is_open = static::checkIsOpen(json_decode($item->working_hours)) == true ? 1 : 0;
        $transformer->num_of_raters = $resturant_branch->rates()->count();
        $transformer->vat = $item->vat;
        $transformer->service_charge = ceil($item->service_charge);

        $transformer->menu_sections = MenuSection::transformCollection($item->active_menu_sections);
        $transformer->cuisines = Cuisine::transformCollection($item->cuisines);
        $transformer->payment_methods = PaymentMethod::transformCollection($item->payment_methods);

        if ($item->offer) {
            $transformer->has_offer = 1;
            $transformer->offer = Offer::transform($item->offer);
        }

        $transformer->rates = Rate::transformCollection($resturant_branch->rates);

        return $transformer;
    }

    public static function transformOneDetails($item) {
        $titleSlug = "title_" . static::getLangCode();
      

        $transformer = new Resturant;
        $transformer->id = $item->id;
        $transformer->title = $item->branch_title ? $item->title . ' - ' . $item->branch_title : $item->title;
        $transformer->slug = $item->slug;
        $transformer->branch_id = $item->branch_id;
        $transformer->image = url('public/uploads/resturantes') . '/' . $item->image;
        $transformer->delivery_time = $item->delivery_time;
        $transformer->delivery_cost = ceil($item->delivery_cost);
        $transformer->minimum_charge = ceil($item->minimum_charge);
        $transformer->rate = $item->rate;
        if ($item->options == 1) {
            $transformer->is_new = 1;
            $transformer->is_ad = 0;
        } elseif ($item->options == 2) {
            $transformer->is_ad = 1;
            $transformer->is_new = 0;
        }
        $transformer->hasVisa = $item->hasVisa() == true ? 1 : 0;
        $transformer->has_offer = $item->has_offer;
        $transformer->working_hours = json_decode($item->working_hours);
        $transformer->is_open = static::checkIsOpen(json_decode($item->working_hours)) == true ? 1 : 0;
        if($item->branch_id){
              $resturant_branch = ResturantBranch::find($item->branch_id);
              $transformer->num_of_raters = $resturant_branch->rates()->count();
        }
        
        $transformer->vat = $item->vat;
        $transformer->service_charge = ceil($item->service_charge);

        $transformer->menu_sections = MenuSection::transformCollection($item->active_menu_sections);
        $transformer->cuisines = Cuisine::transformCollection($item->cuisines);
        $transformer->payment_methods = PaymentMethod::transformCollection($item->payment_methods);
        if ($item->offer) {
            $transformer->has_offer = 1;
            $transformer->offer = Offer::transform($item->offer);
        }
        //$transformer->rates = Rate::transformCollection($item->rates);

        return $transformer;
    }

    public static function transformOnePagination($item) {
        $titleSlug = "title_" . static::getLangCode();
        $transformer = new Resturant;
        $transformer->id = $item->id;
        $transformer->title =  $item->branch_title ? $item->title . ' - ' . $item->branch_title : $item->title;
        $transformer->slug = $item->slug;
        $transformer->image = url("public/uploads/resturantes/$item->image");

        $transformer->minimum_charge = $item->minimum_charge;
        $transformer->rate = $item->rate;
        if ($item->options == 1) {
            $transformer->is_new = 1;
            $transformer->is_ad = 0;
        } elseif ($item->options == 2) {
            $transformer->is_ad = 1;
            $transformer->is_new = 0;
        }
        $transformer->hasVisa = $item->hasVisa() == true ? 1 : 0;

        $transformer->has_offer = ($item->offers > 0) ? true : false;
        $transformer->working_hours = json_decode($item->working_hours);
        $transformer->is_open = static::checkIsOpen(json_decode($item->working_hours)) == true ? 1 : 0;
        //$transformer->num_of_raters = $item->rates()->count();
        $transformer->vat = ceil($item->vat);
        $transformer->service_charge = ceil($item->service_charge);
        $transformer->delivery_time = $item->delivery_time;
        $transformer->delivery_cost = ceil($item->delivery_cost);
        $transformer->cuisines = Cuisine::transformCollection($item->cuisines);
        if($item->branch_id){
              $resturant_branch = ResturantBranch::find($item->branch_id);
              $transformer->num_of_raters = $resturant_branch->rates()->count();
        }

        return $transformer;
    }

    public static function transformFamous($item) {
        $transformer = new Resturant;
        $transformer->title = $item->title;
        $transformer->slug = $item->slug;
        $transformer->image = url("public/uploads/resturantes/$item->image");

        if ($item->options == 1) {
            $transformer->is_new = 1;
            $transformer->is_ad = 0;
        } elseif ($item->options == 2) {
            $transformer->is_ad = 1;
            $transformer->is_new = 0;
        }
        $transformer->is_open = static::checkIsOpen(json_decode($item->working_hours)) == true ? 1 : 0;
        return $transformer;
    }

    public static function transformFooter($item) {
        $transformer = new Resturant;
        $transformer->title = $item->title;
        $transformer->slug = $item->slug;
        return $transformer;
    }


}
