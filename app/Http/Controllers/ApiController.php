<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Helpers\AUTHORIZATION;
use App\Models\User;
use App\Models\Friendship;
use App\Models\UserBlockPost;
use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\Notification;
use App\Models\Setting;
use App\Traits\Basic;
use Request;

class ApiController extends Controller {

    use Basic;

    protected $lang_code;
    protected $User;
    protected $data;
    protected $limit = 10;
    protected $expire_no = 1;
    protected $expire_type = 'day';

    public function __construct() {
        $this->getLangAndSetLocale();
        $this->slugsCreate();
    }

    private function _getLangAndSetLocale() {
        $languages = array('ar', 'en');
        $lang = null;
        if ($this->auth_user() != null) {
            $lang = $this->auth_user()->language;
        }

        if ($lang == null || !in_array($lang, $languages)) {
            $lang = 'ar';
        }
        $this->lang_code = $lang;
        app()->setLocale($lang);
    }

    private function getLangAndSetLocale() {
        $languages = array('ar', 'en');
        $lang = Request::input('lang');
        if ($lang == null || !in_array($lang, $languages)) {
            $lang = 'ar';
        }
        //return _api_json(false,'ssss');
        $this->lang_code = $lang;
        app()->setLocale($lang);
    }

    public function inputs_check($model, $inputs = array(), $id = false, $return_errors = true) {
       
        $errors = array();
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

        return $errors;
    }

    private function slugsCreate() {

        $this->title_slug = 'title_' . $this->lang_code;
        $this->data['title_slug'] = $this->title_slug;
    }

    protected function auth_user() {
        $token = Request::header('authorization');
        $token = Authorization::validateToken($token);
        $user = null;
        if ($token) {
            $user = User::find($token->id);
        }

        return $user;
    }

    

    protected function update_token($device_token, $device_type, $user_id) {
        $User = User::find($user_id);
        $User->device_token = $device_token;
        $User->device_type = $device_type;
        $User->save();
    }

    protected function iniDiffLocations($tableName, $lat, $lng)
    {
        $diffLocations = "SQRT(POW(69.1 * ($tableName.lat - {$lat}), 2) + POW(69.1 * ({$lng} - $tableName.lng) * COS($tableName.lat / 57.3), 2)) as distance";
        return $diffLocations;
    }
     

   

}
