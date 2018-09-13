<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use App\Helpers\AUTHORIZATION;
use App\Models\User;
use App\Models\Setting;
use App\Models\City;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\ContactMessage;
use App\Models\Recommendation;
use App\Models\Offer;
use App\Models\Coupon;
use App\Models\Ad;
use App\Helpers\Fcm;
use Carbon\Carbon;
use DB;

class BasicController extends ApiController {

    private $contact_rules = array(
        'email' => 'required|email',
        'subject' => 'required',
        'message' => 'required',
        'type' => 'required'
    );
    private $recommendation_rules = array(
        'resturant' => 'required',
        'region' => 'required',
    );
    private $coupon_rules = array(
        'coupon' => 'required',
        'resturant_id' => 'required'
    );
    private $offers_rules = array(
        'region' => 'required',
    );
    private $check_email_rules = array(
        'email' => 'required',
        'device_token' => 'required',
        'device_type' => 'required',
    );

    public function check_email(Request $request) {

        $validator = Validator::make($request->all(), $this->check_email_rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _api_json(new \stdClass(), ['errors' => $errors], 422);
        } else {
            //dd(User::all());
            $user = User::where('email', $request->input('email'))->first();
            //dd($user);
            if (!$user) {
                return _api_json(new \stdClass(), ['message' => 'user not found']);
            }
            $token = new \stdClass();
            $token->id = $user->id;
            $token->expire = strtotime('+' . $this->expire_no . $this->expire_type);
            $expire_in_seconds = $token->expire;
            $this->update_token($request->input('device_token'), $request->input('device_type'), $user->id);
            return _api_json(User::transform($user), ['message' => _lang('app.login_done_successfully'), 'token' => AUTHORIZATION::generateToken($token), 'expire' => $expire_in_seconds]);
        }
    }

    public function getToken(Request $request) {
        $token = $request->header('authorization');
        if ($token != null) {
            $token = Authorization::validateToken($token);
            if ($token) {
                $new_token = new \stdClass();
                $find = User::find($token->id);
                if ($find != null) {
                    $new_token->id = $find->id;
                    $new_token->expire = strtotime('+ ' . $this->expire_no . $this->expire_type);
                    $expire_in_seconds = $new_token->expire;
                    return _api_json('', ['token' => AUTHORIZATION::generateToken($new_token), 'expire' => $expire_in_seconds]);
                } else {
                    return _api_json('', ['message' => 'user not found'], 401);
                }
            } else {
                return _api_json('', ['message' => 'invalid token'], 401);
            }
        } else {
            return _api_json('', ['message' => 'token not provided'], 401);
        }
    }

    public function getSettings() {
        try {
            $settings = Setting::get();
            $settings[0]->social_media = json_decode($settings[0]->social_media);
            return _api_json(Setting::transform($settings[0]));
        } catch (\Exception $e) {
            return _api_json(new stdClass(), ['message' => _lang('app.error_is_occured')], 422);
        }
    }

    public function getCities() {
        $cities = array();
        try {
            $cities = City::where('active', 1)->where('parent_id', 0)->orderBy('this_order')->get();
            return _api_json(City::transformCollection($cities));
        } catch (\Exception $e) {
            return _api_json($cities, ['message' => _lang('app.error_is_occured')], 422);
        }
    }

    public function getCuisines() {
        $cuisines = array();
        try {
            $cuisines = Cuisine::where('active', 1)->orderBy('this_order')->select('id', 'title_' . $this->lang_code . ' as title')->get();
            return _api_json($cuisines);
        } catch (\Exception $e) {
            return _api_json($cuisines, ['message' => _lang('app.error_is_occured')], 422);
        }
    }

    public function getCategories() {
        try {
            $categories = Category::where('active', 1)->orderBy('this_order')->select('id', 'title_' . $this->lang_code . ' as title')->get();
            return _api_json($categories);
        } catch (\Exception $e) {
            return _api_json(array(), ['message' => _lang('app.error_is_occured')], 422);
        }
    }

    public function sendContactMessage(Request $request) {
        $validator = Validator::make($request->all(), $this->contact_rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _api_json('', ['errors' => $errors], 422);
        } else {
            try {
                $ContactMessage = new ContactMessage;
                $ContactMessage->email = $request->input('email');
                $ContactMessage->subject = $request->input('subject');
                $ContactMessage->message = $request->input('message');
                $ContactMessage->type = $request->input('type');
                $ContactMessage->save();
                return _api_json('', ['message' => _lang('app.message_is_sent_successfully')]);
            } catch (\Exception $ex) {
                return _api_json('', ['message' => _lang('app.error_is_occured')], 422);
            }
        }
    }

    public function sendRecommendation(Request $request) {
        $validator = Validator::make($request->all(), $this->recommendation_rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _api_json('', ['errors' => $errors], 422);
        } else {
            try {
                $user = $this->auth_user();

                $recommendation = new Recommendation;
                $recommendation->user_id = $user->id;
                $recommendation->resturant_name = $request->resturant;
                $recommendation->region = $request->region;
                $recommendation->save();

                return _api_json('', ['message' => _lang('app.sent_successfully')]);
            } catch (\Exception $ex) {
                return _api_json('', ['message' => _lang('app.error_is_occured')], 422);
            }
        }
    }

    public function getAds() {
        try {
            $ads = Ad::where('active', true)->get();
            return _api_json(Ad::transformCollection($ads));
        } catch (\Exception $ex) {
            return _api_json('', ['message' => _lang('app.error_is_occured')], 422);
        }
    }

    public function getOffers(Request $request) {

        try {

            $validator = Validator::make($request->all(), $this->offers_rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return _api_json('', ['errors' => $errors], 422);
            }

            $offers = Offer::join('resturantes', 'resturantes.id', '=', 'offers.resturant_id')
                    ->join('resturant_branches', 'resturant_branches.resturant_id', '=', 'resturantes.id')
                    ->join('branch_delivery_places', 'resturant_branches.id', '=', 'branch_delivery_places.resturant_branch_id')
                    ->where('branch_delivery_places.region_id', $request->region)
                    //->where('resturant_branches.city_id',$request->city)
                    //->where('resturant_branches.region_id',$request->region)
                    ->where('resturantes.active', true)
                    ->where('resturantes.available', true)
                    ->where('resturant_branches.active', true)
                    ->where('offers.active', true)
                    ->where('offers.available_until', '>', date('Y-m-d'))
                    //->groupBy('resturantes.id')
                    ->select('offers.*', 'resturant_branches.id as resturant_branch_id', 'resturant_branches.title_' . $this->lang_code . ' as branch_title', 'resturantes.title_' . $this->lang_code . ' as resturant_title', 'branch_delivery_places.region_id as region')
                    ->paginate($this->limit);

            return _api_json(Offer::transformCollection($offers));
        } catch (\Exception $ex) {
            return _api_json('', ['message' => _lang('app.error_is_occured')], 422);
        }
    }

    public function checkCoupon(Request $request) {
        try {
            $validator = Validator::make($request->all(), $this->coupon_rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return _api_json('', ['errors' => $errors], 422);
            }
            $coupon = Coupon::where('coupon', $request->coupon)
                    ->where(function ($query) use($request) {
                        $query->where(function ($query2) use($request) {
                            $query2->where('resturant_id', $request->resturant_id);
                            $query2->where('resturant_branch_id', $request->resturant_branch_id);
                        });
                        $query->orWhere('resturant_id', null);
                    })
                    ->where('available_until', '>=', date('Y-m-d'))
                    ->first();
            if ($coupon) {
                return _api_json('', ['discount' => $coupon->discount]);
            }
            return _api_json('', ['message' => _lang('app.this_coupon_is_invalid')],422);
        } catch (\Exception $e) {
            return _api_json('', ['message' => _lang('app.error_is_occured')], 422);
        }
    }

    public function getMyCityRegion(Request $request) {
        try {

            $lat = $request->lat;
            $lng = $request->lng;
            $distance = 1000000;
            $region = City::where('active', true)
                    ->select(
                            'id', "title_$this->lang_code as title", DB::raw($this->iniDiffLocations('cities', $lat, $lng)), DB::raw(" 'region' as type")
                    )
                    ->orderBy('distance')
                    ->where('parent_id', '!=', 0)
                    ->first();

            $city = City::where('active', true)
                    ->select(
                            'id', "title_$this->lang_code as title", DB::raw($this->iniDiffLocations('cities', $lat, $lng)), DB::raw(" 'city' as type")
                    )
                    ->where('parent_id', '=', 0)
                    ->orderBy('distance')
                    ->first();

            return _api_json('', ['city' => $city->id, 'region' => $region->id,'city_title'=>$city->title,'region_title'=>$region->title]);
        } catch (\Exception $e) {
            return _api_json('', ['message' => _lang('app.error_is_occured')], 422);
        }
    }

}
