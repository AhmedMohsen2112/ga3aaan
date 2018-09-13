<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\FrontController;
use App\Models\City;
use App\Models\Resturant;
use App\Models\Topping;
use App\Models\Size;
use App\Models\User;
use App\Models\MenuSection;
use App\Models\Address;
use App\Models\Meal;
use App\Models\MealChoice;
use App\Models\MealSize;
use App\Notifications\GeneralNotification;
use App\Helpers\Fcm;
use App\Mail\GeneralMail;
use Mail;
use Validator;
use Notification;
use DB;

class AjaxController extends FrontController {

    public function search(Request $request) {
        //dd($request->all());
        $city_id = $request->input('city');
        $area_id = $request->input('region');
        $category_id = $request->input('category');
        $long = 7 * 60 * 24;
        return response()->json([
                    'type' => 'success',
                    'message' => _url('resturantes')
                ])->cookie('city_id', $city_id, $long)->cookie('area_id', $area_id, $long)->cookie('category_id', $category_id, $long);
    }

    public function changeLocation(Request $request) {
        $rules = array(
            'city' => 'required',
            'region' => 'required',
            'meal' => 'required',
            'resturant' => 'required',
            'menu_section' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        } else {
            //dd($request->all());
            $meal = $this->getMeal($request->all());
            //dd($meal);
            if (!$meal) {
                session()->flash('errorMessage', _lang('app.sorry_,this_reaturant_doesn\'t_deliver_to_your_address'));
                return _json('error', $request->path());
            } else {
                return _json('success', _url('resturant/' . $meal->resturant_slug . '/' . $meal->menu_section_slug . '/' . $meal->meal_slug));
            }
        }
    }

    public function getRegionByCity($city_id) {

        $regions = City::where('parent_id', $city_id)
                ->where('active', 1)
                ->select('id', 'title_' . $this->lang_code . ' as title')
                ->orderBy('this_order')
                ->get();

        return _json('success', $regions->toArray());
    }

    public function getAddress($address_id) {
        $Address = Address::find($address_id);
        // dd($address_id);
        if ($Address) {
            return _json('success', Address::transform($Address));
        } else {
            return _json('error', _lang('app.error_is_occured'), 400);
        }
    }

    private function getResturantBranchWithCurrentRegion($resturant_id, $region_id) {
        $resturantes = Resturant::join('resturant_branches', 'resturantes.id', '=', 'resturant_branches.resturant_id');
        $resturantes->join('branch_delivery_places', 'resturant_branches.id', '=', 'branch_delivery_places.resturant_branch_id');
        $resturantes->where('resturantes.id', $resturant_id);
        $resturantes->where('branch_delivery_places.region_id', $region_id);
        $resturantes->select('branch_delivery_places.id', 'resturant_branches.slug');
        $resturantes->orderBy('branch_delivery_places.delivery_cost', 'ASC');
        $result = $resturantes->first();
        return $result;
    }

    private function getMeal($where_array) {
        $columns = array('resturant_branches.slug as resturant_slug', 'menu_sections.slug as menu_section_slug', 'meals.slug as meal_slug');
        $meals = Meal::join('menu_sections', 'menu_sections.id', '=', 'meals.menu_section_id');
        $meals->join('resturantes', 'resturantes.id', '=', 'menu_sections.resturant_id');
        $meals->join('resturant_branches', 'resturantes.id', '=', 'resturant_branches.resturant_id');
        $meals->join('branch_delivery_places', 'resturant_branches.id', '=', 'branch_delivery_places.resturant_branch_id');
        $meals->where('resturantes.id', $where_array['resturant']);
        $meals->where('menu_sections.id', $where_array['menu_section']);
        $meals->where('meals.id', $where_array['meal']);
        $meals->where('branch_delivery_places.region_id', $where_array['region']);
        $meals->select($columns);
        $meals = $meals->first();
        return $meals;
    }

    public function getMealOrSizeChoices(Request $request) {
        $where_arr = [];
        $view = '';
        $meal_id = $request->input('meal_id');
        $meal_size_id = $request->input('meal_size_id');
        $columns = array('meals.*');

        if ($meal_size_id) {
            $meals = Meal::join('meal_sizes', 'meal_sizes.meal_id', '=', 'meals.id');
            $meals->join('sizes', 'sizes.id', '=', 'meal_sizes.size_id');
            $meals->where('meal_sizes.id', $meal_size_id);
            $columns[] = 'meal_sizes.price';
            $columns[] = 'sizes.title_ar as size_title_ar';
            $columns[] = 'sizes.title_en as size_title_en';
        } else {
            $columns[] = 'meals.price';
            $meals = Meal::where('meals.id', $request->input('meal_id'));
        }
        $meals->select($columns);
        $meal = $meals->first();

        if ($meal) {
            $meal = Meal::transformForChoices($meal);
            //dd($meal);
            $this->data['meal'] = $meal;

            if ($request->input('meal_size_id')) {
                $where_arr['meal_size_id'] = $request->input('meal_size_id');
            } else {
                $where_arr['meal_id'] = $request->input('meal_id');
            }
            $choices = Meal::getMealSizeChoices($where_arr);
            //dd($choices);
            $this->data['choices'] = $choices;
            $view = $this->_view('ajax.choices');
            $view = $view->render();
        }

        return $view;
    }

    public function getMealOrSizeChoices2(Request $request) {
        $where_arr = [];
        $view = '';
        $meal_id = $request->input('meal_id');
        $meal_size_id = $request->input('meal_size_id');

        $meal = Meal::leftJoin('meal_sizes', 'meals.id', '=', 'meal_sizes.meal_id')
                ->select(['meals.*', DB::raw('(CASE WHEN meal_sizes.id IS NULL THEN NULL ELSE meal_sizes.price END) AS size_price')])
                ->whereRaw("CASE WHEN meal_sizes.id IS NULL THEN FALSE ELSE meal_sizes.id=$meal_size_id END ")
                ->orWhere('meals.id', $meal_id)
                ->first();
        dd($meal);
        if ($meal) {
            $meal = Meal::transformForChoices($meal);
            $this->data['meal'] = $meal;

            if ($request->input('meal_size_id')) {
                $where_arr['meal_size_id'] = $request->input('meal_size_id');
            } else {
                $where_arr['meal_id'] = $request->input('meal_id');
            }
            $choices = Meal::getMealSizeChoices($where_arr);
            //dd($choices);
            $this->data['choices'] = $choices;
            $view = $this->_view('ajax.choices');
            $view = $view->render();
        }

        return $view;
    }

}
