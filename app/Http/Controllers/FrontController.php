<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\Basic;
use Auth;
use App\Models\Setting;
use App\Models\Resturant;
use App\Models\Cuisine;
use App\Models\Category;
use App\Models\City;

class FrontController extends Controller {

    use Basic;

    protected $lang_code;
    protected $User = false;
    protected $isUser = false;
    protected $_Request = false;
    protected $limit = 6;
    protected $order_minutes_limit = 3;
    protected $data = array();

    public function __construct() {
        if (Auth::guard('web')->user() != null) {
            $this->User = Auth::guard('web')->user();
            $this->isUser = true;
        }
        $this->data['User'] = $this->User;
        $this->data['isUser'] = $this->isUser;
        $segment2 = \Request::segment(2);
        $this->data['page_link_name'] = $segment2;
        $this->data['order_minutes_limit'] = $this->order_minutes_limit;

        $this->getLangCode();
        $this->getSettings();
        $this->data['cuisines_footer'] = $this->getCuisinesFooter();
        $this->data['resturantes_footer'] = $this->getResturantesFooter();
        $this->data['page_title'] = 'Ga3aaan';
    
        
    }

    private function getLangCode() {
        $this->lang_code = app()->getLocale();
        $this->data['lang_code'] = $this->lang_code;
        session()->put('lang_code', $this->lang_code);
        if ($this->data['lang_code'] == 'ar') {
            $this->data['next_lang_code'] = 'en';
            $this->data['next_lang_text'] = 'English';
            $this->data['currency_sign'] = 'جنيه';
        } else {
            $this->data['next_lang_code'] = 'ar';
            $this->data['next_lang_text'] = 'العربية';
            $this->data['currency_sign'] = 'EGP';
        }
        $this->slugsCreate();
    }

    protected function iniDiffLocations($tableName, $lat, $lng) {
        $diffLocations = "SQRT(POW(69.1 * ($tableName.lat - {$lat}), 2) + POW(69.1 * ({$lng} - $tableName.lng) * COS($tableName.lat / 57.3), 2)) as distance";
        return $diffLocations;
    }

    private function getSettings() {
        $this->data['settings'] = Setting::select('about_us_' . $this->lang_code . ' as about_us', 'usage_conditions_' . $this->lang_code . ' as usage_conditions', 'terms_conditions_' . $this->lang_code . ' as terms_conditions', 'social_media', 'android_url', 'ios_url')
                ->first();
    }

    private function getResturantesFooter() {
        $Resturants = Resturant::join('resturant_branches', 'resturantes.id', '=', 'resturant_branches.resturant_id')
                ->where('resturantes.active', 1)
                ->where('resturantes.available', 1)
                ->select(["resturantes.title_$this->lang_code  as title", "resturantes.slug",])
                ->groupBy('resturantes.id')
                ->take(6)
                ->get();
        return $Resturants;
    }

    private function getCuisinesFooter() {

        $cuisines = Cuisine::join('resturant_cuisines', 'cuisines.id', '=', 'resturant_cuisines.cuisine_id')
                ->join('resturantes', 'resturantes.id', '=', 'resturant_cuisines.resturant_id')
                ->groupBy('cuisines.id')
                ->select(["cuisines.id", "cuisines.title_$this->lang_code as title", "cuisines.slug"])
                ->take(6)
                ->get();
        return $cuisines;
    }

    protected function _view($main_content, $type = 'front') {
        $main_content = "main_content/$type/$main_content";
        //dd($main_content);
        return view($main_content, $this->data);
    }

    public function inputs_check($model, $inputs = array(), $id = false, $return_errors = true, $together = false) {
        $errors = array();
        if ($together) {
            $where_array = $inputs;
            if ($id) {
                $where_array[] = array('id', '!=', $id);
            }
            $find = $model::where($where_array)->get();

            if (count($find)) {

                $errors[$key] = array(_lang('app.' . $key) . ' ' . _lang("app.added_before"));
            }
        } else {

            foreach ($inputs as $key => $value) {
                $where_array = array();
                $where_array[] = array($key, '=', $value);
                if ($id) {
                    $where_array[] = array('id', '!=', $id);
                }

                $find = $model::where($where_array)->get();

                if (count($find)) {

                    $errors[$key] = array(_lang('app.' . $key) . ' ' . _lang("app.added_before"));
                }
            }
        }
        return $errors;
    }

    protected function _upload($file, $path) {
        $image = '';
        $path = public_path() . "/uploads/$path";
        $filename = time() . mt_rand(1, 1000000) . '.' . strtolower($file->getClientOriginalExtension());
        if ($file->move($path, $filename)) {
            $image = $filename;
        }
        return $image;
    }

    protected function err404($code = false, $message = false) {
        if (!$message) {
            $message = _lang('app.page_not_found');
        }
        if (!$code) {
            $code = 404;
        }
        $this->data['code'] = $code;
        $this->data['message'] = $message;
        return $this->_view('err404');
    }

    protected function getCategories() {
        $categories = Category::where('active', 1)
                ->orderBy('this_order', 'ASC')
                ->select(["id", "title_$this->lang_code as title"])
                ->get();
        return $categories;
    }

    protected function getCuisines() {
        $cuisines = Cuisine::where('cuisines.active', 1)
                ->orderBy('this_order', 'ASC')
                ->select(["id", "title_$this->lang_code as title"])
                ->get();
        return Cuisine::transformCollection($cuisines, 'ResturantesPage');
    }

    protected function getCities() {
        $cities = City::join('resturant_branches', 'cities.id', '=', 'resturant_branches.city_id')
                ->join('resturantes', 'resturantes.id', '=', 'resturant_branches.resturant_id')
                ->groupBy('cities.id')
                ->select(["cities.id", "cities.title_$this->lang_code as title"])
                ->orderBy('this_order')
                ->get();
        return $cities;
    }

    protected function getCart() {
        $cart = \Cookie::get('cart');

        if (!$cart) {
            $cart = array(
                'info' => array(),
                'items' => array(),
                'price_list' => array()
            );
        } else {
            $cart = unserialize($cart);
        }
        //dd($cart);
        if (request()->input('step') == 1) {
            //dd('here');
            $cart = $this->clear_coupon($cart);
        }

        return $cart;
    }

    protected function clear_coupon($cart) {
        if (isset($cart['info']['coupon'])) {
            unset($cart['info']['coupon']);
            unset($cart['price_list']['coupon_cost']);
            unset($cart['price_list']['net_price']);
        }
        return $cart;
    }

}
