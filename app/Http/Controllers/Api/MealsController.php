<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Meal;
use App\Models\MenuSection;
use Validator;
use DB;
use App\Models\Favourite;

class MealsController extends ApiController {

    private $rules = array(
        'menu_section' => 'required',
    );
    private $meal_rules = array(
        'resturant_branch_id' => 'required',
        'region' => 'required',
        'resturant' => 'required'
    );
    private $favourite_rules = array(
        'resturant_branch_id' => 'required',
        'meal_id' => 'required'
    );

    public function __construct() {
        parent::__construct();
    }

    protected function index(Request $request) {

        $meals = array();
        $validator = Validator::make($request->all(), $this->rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _api_json($meals, ['errors' => $errors], 422);
        } else {
            try {

                $user = $this->auth_user();

                $meals = Meal::where('menu_section_id', $request->menu_section);
                if ($user) {

                    $meals->leftJoin('favourites', function ($join) use($request, $user) {
                        $join->on('favourites.meal_id', '=', 'meals.id');
                        $join->where('favourites.user_id', '=', $user->id);
                        $join->where('favourites.resturant_branch_id', '=', $request->resturant_branch_id);
                    });
                    $meals->select('meals.*', 'favourites.id as is_favourite');
                } else {
                    $meals->select('meals.*');
                }
                $meals->where('meals.active', true);
                $meals->orderBy('meals.this_order');
                $meals = $meals->paginate($this->limit);

                $meals = Meal::transformCollection($meals, 'Menu_section');
                return _api_json($meals);
            } catch (\Exception $e) {
                dd($e);
                $message = _lang('app.error_is_occured');
                return _api_json($meals, ['message' => $message], 422);
            }
        }
    }

    public function show(Request $request, $id) {

        try {
            $validator = Validator::make($request->all(), $this->meal_rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return _api_json(new \stdClass(), ['errors' => $errors], 422);
            }
            $user = $this->auth_user();
            $meal = Meal::join('menu_sections', 'meals.menu_section_id', '=', 'menu_sections.id')
                    ->join('resturantes', 'menu_sections.resturant_id', '=', 'resturantes.id')
                    ->join('resturant_branches', 'resturant_branches.resturant_id', '=', 'resturantes.id')
                    ->join('branch_delivery_places', 'resturant_branches.id', '=', 'branch_delivery_places.resturant_branch_id')
                    ->where('resturantes.id', $request->resturant)
                    //->where('resturant_branches.city_id',$request->city)
                    //->where('resturant_branches.region_id',$request->region)
                    ->where('branch_delivery_places.region_id', $request->region)
                    ->where('resturant_branches.id', $request->resturant_branch_id)
                    ->where('meals.id', $id)
                    ->where('meals.active', true)
                    ->select('meals.*', 'resturantes.service_charge', 'resturantes.vat', 'branch_delivery_places.delivery_cost')
                    ->first();
            if (!$meal) {
                $message = _lang('app.not_found');
                return _api_json(new \stdClass(), ['message' => $message], 404);
            }
            $meal = Meal::transform($meal, $user);
            return _api_json($meal);
        } catch (\Exception $e) {
            dd($e);
            $message = _lang('app.error_is_occured');
            return _api_json(new \stdClass(), ['message' => $message], 422);
        }
    }

    public function addDeleteFavourite(Request $request) {
        try {
            $validator = Validator::make($request->all(), $this->favourite_rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return _api_json($meals, ['errors' => $errors], 422);
            }

            $user = $this->auth_user();
            $Meal = Meal::find($request->meal_id);
            if (!$Meal) {
                $message = _lang('app.not_found');
                return _api_json('', ['message' => $message], 404);
            }

            $check = Favourite::where('meal_id', $request->meal_id)
                    ->where('user_id', $user->id)
                    ->where('resturant_branch_id', $request->resturant_branch_id)
                    ->first();
            if ($check) {
                $check->delete();
            } else {
                $favourite = new Favourite;
                $favourite->meal_id = $request->meal_id;
                $favourite->resturant_branch_id = $request->resturant_branch_id;
                $favourite->user_id = $user->id;
                $favourite->save();
            }
            return _api_json('');
        } catch (\Exception $e) {

            $message = _lang('app.error_is_occured');
            return _api_json('', ['message' => $message], 422);
        }
    }

    public function choices(Request $request) {

        if ($request->input('meal_size_id')) {
            $where_arr['meal_size_id'] = $request->input('meal_size_id');
        } else {
            $where_arr['meal_id'] = $request->input('meal_id');
        }
        $choices = Meal::getMealSizeChoices($where_arr,'api');
        return _api_json($choices);
    }

}
