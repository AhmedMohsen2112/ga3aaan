<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;
use App\Models\Meal;
use App\Models\MealSize;
use App\Models\MenuSection;
use App\Models\Resturant;
use App\Models\Size;
use App\Models\MealTopping;
use DB;
use Validator;
use Session;

class ResturantMealsController extends BackendController {

    public function __construct() {
        parent::__construct();
        $this->middleware('CheckPermission:resturant_meals,open', ['only' => ['index']]);
        $this->middleware('CheckPermission:resturant_meals,view', ['only' => ['show']]);
    }

    public function index(Request $request) {
        $menu_section_id = $request->input('menu_section');
        $menu_section = $this->getMenuSection($menu_section_id);
        if(!$menu_section){
            return $this->err404();
        }
        $this->data['menu_section'] = $menu_section;
        return $this->_view('resturant_meals/index', 'backend');
    }

    public function show($id) {
        $meal = $this->getMeal($id);
        if(!$meal){
            return $this->err404();
        }
       $this->data['meal'] = $meal;
       $this->data['meal_sizes'] = $this->getMealSizes($id);
       $this->data['meal_toppings'] = $this->getMealToppings($id);
//      dd($this->data['meal_toppings']);
        return $this->_view('resturant_meals/view', 'backend');
    }

    public function data(Request $request) {
        $menu_section_id = $request->input('menu_section');
        $resturant_meals = Meal::where('menu_section_id', $menu_section_id)
                ->select([
            'id', "title_" . $this->lang_code . " as title",'image', "this_order", 'active'
        ]);

        return \Datatables::eloquent($resturant_meals)
                        ->editColumn('image', function ($item) {
                            $back = '<img src="' . url('public/uploads/meals/' . $item->image) . '" style="height:64px;width:64px;"/>';
                            return $back;
                        })
                         ->editColumn('active', function ($item) {
                                    if ($item->active == 1) {
                                        $message = _lang('app.active');
                                        $class = 'btn-info';
                                    } else {
                                        $message = _lang('app.not_active');
                                        $class = 'btn-danger';
                                    }
                                    $back = '<a class="btn ' . $class . '" onclick = "ResturantMeals.status(this);return false;" data-id = "' . $item->id . '" data-status = "' . $item->active . '">' . $message . ' <a>';
                                    return $back;
                                })
                        ->addColumn('options', function ($item) {

                            $back = "";
                            if (\Permissions::check('resturant_meals', 'view')) {
                                $back .= '<a class="btn btn-info" href="' . url('admin/resturant_meals/' . $item->id ) . '">';
                                $back .= _lang('app.view');
                                $back .= '</a>';
                            }

                            return $back;
                        })
                        ->escapeColumns([])
                        ->make(true);
    }


    public function status($id) {
        $data = array();
        $meal = Meal::join('menu_sections','menu_sections.id','=','meals.menu_section_id')
             ->join('resturantes','menu_sections.resturant_id','=','resturantes.id')
             ->where('resturantes.admin_id',$this->User->id)
             ->where('meals.id',$id)
             ->select('meals.*')
             ->first();
       
        if ($meal != null) {
            if ($meal->active == true) {
                $meal->active = false;
                $data['status'] = false;
            } else {
                $meal->active = true;
                $data['status'] = true;
            }
            $meal->save();

            return $data;
        } else {
            return $data;
        }
    }

    private function getMenuSection($id) {
        $menu_section = MenuSection::join('resturantes','resturantes.id','=','menu_sections.resturant_id')
                ->where('resturantes.id', $this->User->resturant->id)
                ->where('menu_sections.id', $id)
                ->select('menu_sections.id', 'menu_sections.title_' . $this->lang_code . ' as title', 'menu_sections.resturant_id')->first();
        return $menu_section;
    }
    private function getMeal($id) {
        $Meal = Meal::join('menu_sections','menu_sections.id','=','meals.menu_section_id')
                ->join('resturantes','resturantes.id','=','menu_sections.resturant_id')
                ->where('resturantes.id', $this->User->resturant->id)
                ->where('meals.id', $id)
                ->select('meals.id', 'meals.title_' . $this->lang_code . ' as title','meals.description_' . $this->lang_code . ' as description','meals.image')->first();
        //dd($Meal->sizes);
        return $Meal;
    }
    private function getMealSizes($id) {
        $MealSizes = Meal::join('meal_sizes','meals.id','=','meal_sizes.meal_id')
                ->join('sizes','sizes.id','=','meal_sizes.size_id')
                ->where('meals.id', $id)
                ->select('sizes.id', 'sizes.title_' . $this->lang_code . ' as title','meal_sizes.price')
                ->get();
        //dd($Meal->sizes);
        return $MealSizes;
    }
    private function getMealToppings($id) {
        $Meal = Meal::join('meal_toppings','meals.id','=','meal_toppings.meal_id')
                ->join('menu_section_toppings','menu_section_toppings.id','=','meal_toppings.menu_section_topping_id')
                ->join('menu_sections','menu_sections.id','=','menu_section_toppings.menu_section_id')
                ->join('toppings','toppings.id','=','menu_section_toppings.topping_id')
                ->where('meals.id', $id)
                ->select('toppings.title_' . $this->lang_code . ' as title','menu_section_toppings.price')
                ->get();
        //dd($Meal->sizes);
        return $Meal;
    }

}
