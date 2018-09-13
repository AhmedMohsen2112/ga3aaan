<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\FrontController;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Pagination\LengthAwarePaginator;
use DB;
use App\Models\Resturant;
use App\Models\MenuSection;
use App\Models\Meal;
use App\Models\Cuisine;
use App\Models\Category;
use App\Models\Recommendation;
use App\Models\ResturantBranch;
use App\Models\City;
use App\Models\Rate;
use PulkitJalan\GeoIP\GeoIP;
use Validator;

class ResturantesController extends FrontController {

    private $suggest_rules = array(
        'resturant_name' => 'required',
        'resturant_region' => 'required'
    );

    public function __construct() {
        parent::__construct();
        $this->middleware('auth', ['only' => ['suggest']]);
    }

    public function index(Request $request) {
        $region=null;
        if (\Cookie::get('city_id') !== null && \Cookie::get('area_id') !== null) {
            $city_id = \Cookie::get('city_id');
            $area_id = \Cookie::get('area_id');

            $where_array['city_id'] = $city_id;
            $where_array['region_id'] = $area_id;
             $region=City::where('id',$area_id)->select('id',"title_$this->lang_code as title","parent_id")->first();
        }
        //dd(\Cookie::get('category_id'));
        if (\Cookie::get('category_id') !== null) {
            $category_id = \Cookie::get('category_id');
            $where_array['category_id'] = $category_id;
        }

        $where_array['filter'] = $request->all();
        $this->data['resturantes'] = $this->getResturantes($where_array, 'transformOnePagination');
        $this->data['cuisines'] = $this->getCuisines();
        $this->data['categories'] = $this->getCategories();
        $this->data['filter'] = $where_array['filter'];
        $this->data['page_title'] = _lang('app.resturantes');
        $this->data['cities'] = $this->getCities();
        $this->data['region'] = $region;

        return $this->_view('resturantes.index');
    }

    public function getMyLocation(Request $request) {
        $geoip = new GeoIP();

        $geoip->setIp($request->ip());

        $lat = $geoip->getLatitude();
        $lng = $geoip->getLongitude();


        $region = City::where('active', true)
                ->select(
                        'id', DB::raw($this->iniDiffLocations('cities', $lat, $lng))
                )
                ->orderBy('distance')
                ->where('parent_id', '!=', 0)
                ->first();

        $city = City::where('active', true)
                ->select(
                        'id', DB::raw($this->iniDiffLocations('cities', $lat, $lng))
                )
                ->where('parent_id', '=', 0)
                ->orderBy('distance')
                ->first();

        return redirect()->route('show_resturantes')
                        ->withCookie(cookie('city_id', $city->id))
                        ->withCookie(cookie('area_id', $region->id));
    }

    public function suggest(Request $request) {

        $validator = Validator::make($request->all(), $this->suggest_rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();

            if ($request->ajax()) {

                return _json('error', $errors);
            } else {
                return redirect()->back()->withInput($request->all())->withErrors($errors);
            }
        } else {

            try {

                //dd($request->all());
                $Recommendation = new Recommendation;
                $Recommendation->resturant_name = $request->resturant_name;
                $Recommendation->region = $request->resturant_region;
                $Recommendation->user_id = $this->User->id;

                $Recommendation->save();
                $message = _lang('app.sending_successfully');
                if ($request->ajax()) {
                    return _json('success', $message);
                } else {
                    return redirect()->back()->withInput($request->all())->with(['successMessage' => $message]);
                }
            } catch (\Exception $e) {
                $message = $e->getMessage();
                if ($request->ajax()) {
                    return _json('error', $message);
                } else {
                    return redirect()->back()->withInput($request->all())->with(['errorMessage' => $message]);
                }
            }
        }
    }

    public function getResturantesByCuisine(Request $request, $slug) {
        $region=null;
        if (\Cookie::get('city_id') !== null && \Cookie::get('area_id') !== null) {
            $city_id = \Cookie::get('city_id');
            $area_id = \Cookie::get('area_id');
            $where_array['city_id'] = $city_id;
            $where_array['region_id'] = $area_id;
            $region=City::where('id',$area_id)->select('id',"title_$this->lang_code as title","parent_id")->first();
        }
        $cuisine = Cuisine::where('slug', $slug)->first();
        if (!$cuisine) {
            return $this->err404();
        }
        $where_array['cuisine_slug'] = $slug;
        $this->data['cuisine'] = $cuisine;
        $this->data['resturantes'] = $this->getResturantes($where_array, 'transformOnePagination');
        $this->data['cuisines'] = $this->getCuisines();
        $this->data['categories'] = $this->getCategories();
        $this->data['cities'] = $this->getCities();
        $this->data['region'] = $region;
        return $this->_view('resturantes.index');
    }

    public function resturant(Request $request, $slug) {
        $where_array = array();
        $check = $this->checkResturantOrBranch($slug);
        if ($check && $check->type == 'resturant') {
            if (\Cookie::get('city_id') !== null && \Cookie::get('area_id') !== null) {
                $city_id = \Cookie::get('city_id');
                $area_id = \Cookie::get('area_id');
                $resturant_with_current_branch = $this->getResturantBranchWithCurrentRegion($slug, $area_id);
                if ($resturant_with_current_branch) {
                    return redirect(_url('resturant/' . $resturant_with_current_branch->slug));
                }
            }
            $where_array['resturant_slug'] = $slug;
        } else {
            $where_array['resturant_branch_slug'] = $slug;
        }

        //dd($where_array);
        $resturant = $this->getResturantes($where_array, 'transformOneDetails');
        //dd($resturant);
        if (!$resturant) {
            return $this->err404();
        }
        $this->data['page_title'] = $resturant->title;
        $this->data['resturant'] = $resturant;
        return $this->_view('resturantes.view');
    }

    public function resturant_info(Request $request, $slug) {
        $resturant = ResturantBranch::join('resturantes', 'resturantes.id', '=', 'resturant_branches.resturant_id')
                ->where('resturant_branches.slug', $slug)
                ->select('resturantes.image', 'resturantes.working_hours', 'resturant_branches.rate', 'resturantes.title_' . $this->lang_code . ' as resturant_title', 'resturant_branches.title_' . $this->lang_code . ' as branch_title', 'resturant_branches.id', DB::raw('(select count(*) from rates where rates.resturant_branch_id = resturant_branches.id) as num_of_raters'))
                ->first();

        $resturant->working_hours = json_decode($resturant->working_hours, true);
        $resturant_branch_rates = Rate::where('resturant_branch_id', $resturant->id)->get();

        $this->data['page_title'] = $resturant->resturant_title . ' - ' . $resturant->branch_title;
        $this->data['resturant_branch_rates'] = Rate::transformCollection($resturant_branch_rates);
        $this->data['resturant'] = $resturant;

        return $this->_view('resturantes.info');
    }

    public function menu(Request $request, $resturant, $menu) {
        $check = $this->checkResturantOrBranch($resturant);
        if (!$check) {
            return $this->err404();
        }
        $where_array['menu_section'] = $menu;
        if ($check->type == 'resturant') {
            $where_array['resturant_slug'] = $check->slug;
        }
        if ($check->type == 'branch') {
            $where_array['resturant_branch_slug'] = $check->slug;
            $where_array['resturant_branch_id'] = $check->id;
        }
        $meals = $this->getMeals($where_array, 'transformForPagination');

        $this->data['menu_section'] = MenuSection::where('slug', $menu)->select('title_' . $this->lang_code . ' as title')->first();
        $this->data['page_title'] = $this->data['menu_section']->title;
        $this->data['meals'] = $meals;
        return $this->_view('resturantes.meals');
    }

    public function meal(Request $request, $resturant, $menu, $meal) {
        $check = $this->checkResturantOrBranch($resturant);
        if (!$check) {
            return $this->err404();
        }
        $where_array['meal'] = $meal;
        $where_array['menu_section'] = $menu;
        if ($check->type == 'resturant') {
            $where_array['resturant_slug'] = $check->slug;
        }
        if ($check->type == 'branch') {
            $where_array['resturant_branch_slug'] = $check->slug;
            $where_array['resturant_branch_id'] = $check->id;
        }

        $meals = $this->getMeals($where_array, 'transformForDetails');


        $meals->config['in_same_branch'] = true;
        $cart = $this->getCart();
        if (isset($cart['info']['resturant_branch_id'])) {
            if ($meals->resturant_branch_id && ($meals->resturant_branch_id != $cart['info']['resturant_branch_id'])) {
                $meals->config['in_same_branch'] = false;
            }
        }

        //dd($cart['info']['resturant_branch_id']);
        $this->data['cities'] = $this->getCities();

        $this->data['categories'] = $this->getCategories();
        $this->data['meal'] = $meals;

        //$titleSlug = "title_" . $this->lang_code;
        $this->data['page_title'] = $meals->title;

//        dd($this->data['meal']);
        return $this->_view('resturantes.meal');
    }

    private function getResturantes($where_array, $transform_type = "transformOnePagination") {
        $columns = ["resturantes.*", "resturant_branches.rate", "resturantes.title_$this->lang_code as title",
            'branch_delivery_places.delivery_cost', DB::raw("(SELECT Count(*) FROM offers WHERE resturant_id = resturantes.id and active = 1 and available_until > " . date('Y-m-d') . ") as offers")];
        //for resturantes
        if (isset($where_array['region_id'])) {
            $columns[] = "resturant_branches.id as branch_id";
            $columns[] = "resturant_branches.title_$this->lang_code as branch_title";
            $columns[] = "resturant_branches.slug as slug";
        }
        //for one resturant
        if (isset($where_array['resturant_branch_slug'])) {
            $columns[] = "resturant_branches.id as branch_id";
            $columns[] = "resturant_branches.title_$this->lang_code as branch_title";
            $columns[] = "resturant_branches.slug as slug";
        }
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
        //dd($where_array);
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

        if (!isset($where_array['region_id'])) {
            $resturantes->groupBy('resturantes.id');
        }
        $resturantes->groupBy('resturant_branches.id');
        $resturantes->orderBy('branch_delivery_places.delivery_cost', 'ASC');
        $resturantes->select($columns);
        if ($transform_type == "transformOnePagination") {
            $resturantes = $resturantes->paginate($this->limit);

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

    private function getMeals($where_array, $transform_type = "transformForPagination") {
        $user = $this->User;
        $columns = array('meals.*', 'resturantes.working_hours', 'resturantes.id as resturant_id', 'menu_sections.slug as menu_section_slug',
            'resturantes.service_charge', 'resturantes.vat', 'branch_delivery_places.delivery_cost');
        if (isset($where_array['resturant_slug'])) {
            $columns[] = 'resturantes.slug as resturant_slug';
        }
        if (isset($where_array['resturant_branch_slug'])) {
            $columns[] = 'resturant_branches.slug as resturant_slug';
            $columns[] = 'resturant_branches.id as resturant_branch_id';
        }
        $meals = Meal::join('menu_sections', 'menu_sections.id', '=', 'meals.menu_section_id');
        $meals->join('resturantes', 'resturantes.id', '=', 'menu_sections.resturant_id');
        $meals->join('resturant_branches', 'resturantes.id', '=', 'resturant_branches.resturant_id');
        $meals->join('branch_delivery_places', 'resturant_branches.id', '=', 'branch_delivery_places.resturant_branch_id');
        if ($this->User && isset($where_array['resturant_branch_slug'])) {
            $columns[] = 'favourites.id as favourite_id';
            $meals->leftJoin('favourites', function ($join) use($where_array, $user) {
                $join->on('favourites.meal_id', '=', 'meals.id');
                $join->where('favourites.user_id', $user->id);
                $join->where('favourites.resturant_branch_id', $where_array['resturant_branch_id']);
            });
        }
        if (isset($where_array['resturant_slug'])) {
            $meals->where('resturantes.slug', $where_array['resturant_slug']);
        }
        if (isset($where_array['resturant_branch_slug'])) {
            $meals->where('resturant_branches.slug', $where_array['resturant_branch_slug']);
        }

        $meals->where('menu_sections.slug', $where_array['menu_section']);
        if (isset($where_array['meal'])) {
            $meals->where('meals.slug', $where_array['meal']);
        }
        $meals->where('meals.active', 1);
        $meals->groupBy('meals.id');
        $meals->orderBy('meals.this_order');
        $meals->select($columns);
        if ($transform_type == "transformForPagination") {
            $meals = $meals->paginate(8);
            $meals->getCollection()->transform(function($meal, $key) use($transform_type) {
                return Meal::$transform_type($meal);
            });
        } else {
            $meals = $meals->first();
            if ($meals) {
                $meals = Meal::$transform_type($meals);
            }
        }


        return $meals;
    }

    private function getMeals2($where_array, $transform_type = "transformForPagination") {
        $user = $this->User;
        $columns = array('meals.*', 'resturantes.id as resturant_id', 'resturant_branches.slug as resturant_slug', 'menu_sections.slug as menu_section_slug',
            'resturantes.service_charge', 'resturantes.vat', 'resturant_branches.delivery_cost', 'resturant_branches.id as resturant_branch_id');
        $meals = Meal::join('menu_sections', 'menu_sections.id', '=', 'meals.menu_section_id');
        $meals->join('resturantes', 'resturantes.id', '=', 'menu_sections.resturant_id');
        $meals->join('resturant_branches', 'resturantes.id', '=', 'resturant_branches.resturant_id');
        if ($this->User) {
            $columns[] = 'favourites.id as favourite_id';
            $meals->leftJoin('favourites', function ($join) use($user) {
                $join->on('favourites.meal_id', '=', 'meals.id')
                        ->where('favourites.user_id', $user->id);
            });
        }

        $meals->where('resturant_branches.slug', $where_array['resturant']);
        $meals->where('menu_sections.slug', $where_array['menu_section']);
        if (isset($where_array['meal'])) {
            $meals->where('meals.slug', $where_array['meal']);
        }
        $meals->where('meals.active', 1);
        $meals->groupBy('meals.id');
        $meals->select($columns);
        if ($transform_type == "transformForPagination") {
            $meals = $meals->paginate($this->limit);
            $meals->getCollection()->transform(function($meal, $key) use($transform_type) {
                return Meal::$transform_type($meal);
            });
        } else {
            $meals = $meals->first();
            if ($meals) {
                $meals = Meal::$transform_type($meals);
            }
        }


        return $meals;
    }

    private function checkResturantOrBranch($slug) {
        $client = Resturant::where('slug', $slug)
                ->select('id', 'slug', DB::RAW("'resturant' as type"));
        $find = ResturantBranch::where('slug', $slug)
                ->select('id', 'slug', DB::RAW("'branch' as type"))
                ->union($client)
                ->get();
        if ($find->count() > 0) {
            $find = $find[0];
        }
        return $find;
    }

    private function getResturantBranchWithCurrentRegion($slug, $region) {
        $resturantes = Resturant::join('resturant_branches', 'resturantes.id', '=', 'resturant_branches.resturant_id');
        $resturantes->join('branch_delivery_places', 'resturant_branches.id', '=', 'branch_delivery_places.resturant_branch_id');
        $resturantes->where('resturantes.slug', $slug);
        $resturantes->where('branch_delivery_places.region_id', $region);
        $resturantes->select('branch_delivery_places.id', 'resturant_branches.slug');
        $resturantes->orderBy('branch_delivery_places.delivery_cost', 'ASC');
        $result = $resturantes->first();
        return $result;
    }

}
