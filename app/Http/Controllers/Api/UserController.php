<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Helpers\AUTHORIZATION;
use App\Models\User;
use App\Models\Meal;
use App\Models\Order;
use App\Models\Favourite;
use Validator;

class UserController extends ApiController {

    private $orders_rules = array(
        'type' => 'required'
    );

    public function __construct() {
        parent::__construct();
    }

    public function show() {
        try {
            $User = $this->auth_user();
            return _api_json(User::transform($User));
        } catch (\Exception $e) {
            $message = _lang('app.error_is_occured');
            return _api_json(new \stdClass(), ['message' => $message], 422);
        }
    }

    protected function update(Request $request) {
        $User = $this->auth_user();
        $rules = array();
        if ($request->input('first_name')) {
            $rules['first_name'] = "required";
        }
        if ($request->input('last_name')) {
            $rules['last_name'] = "required";
        }
        if ($request->input('email')) {
            $rules['email'] = "required|email|unique:users,email,$User->id";
        }
        if ($request->input('mobile')) {
            $rules['mobile'] = "required|unique:users,mobile,$User->id";
        }
        if ($request->input('old_password')) {
            $rules['password'] = "required";
            $rules['confirm_password'] = "required|same:password";
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _api_json('', ['errors' => $errors], 422);
        } else {

            try {

                if ($request->input('first_name')) {
                    $User->first_name = $request->input('first_name');
                }
                if ($request->input('last_name')) {
                    $User->last_name = $request->input('last_name');
                }
                if ($request->input('mobile')) {
                    $User->mobile = $request->input('mobile');
                }
                if ($request->input('email')) {
                    $User->email = $request->input('email');
                }
                if ($request->input('sms_notify')) {
                    $User->sms_notify = $request->input('sms_notify');
                }
                if ($request->input('email_notify')) {
                    $User->email_notify = $request->input('email_notify');
                }
                if ($old_password = $request->input('old_password')) {
                    if (!password_verify($old_password, $User->password)) {
                        return _api_json('', ['message' => _lang('app.invalid_old_password')], 422);
                    } else {
                        $User->password = bcrypt($request->input('password'));
                    }
                }
                if ($request->input('user_image')) {
                    $file = public_path("uploads/users/$User->user_image");
                    if (!is_dir($file)) {
                        if (file_exists($file)) {
                            unlink($file);
                        }
                    }

                    $User->user_image = img_decoder($request->input('user_image'), 'users');
                }
                $User->save();
                return _api_json(User::transform($User), ['message' => _lang('app.updated_successfully')]);
            } catch (\Exception $e) {
                $message = _lang('app.error_is_occured');
                return _api_json(new \stdClass(), ['message' => $message], 422);
            }
        }
    }

    public function logout() {
        try {

            $user = $this->auth_user();
            $user->device_token = "";
            $user->save();
            return _api_json('');
        } catch (\Exception $e) {
            $message = ['message' => _lang('app.error_occured')];
            return _api_json('', $message, 422);
        }
    }

    public function favourites(Request $request) {
        $favourites = array();
        try {

            $user = $this->auth_user();

            $favourites = Favourite::join('resturant_branches', 'favourites.resturant_branch_id', '=', 'resturant_branches.id')
                    ->join('meals', 'favourites.meal_id', '=', 'meals.id')
                    ->join('resturantes', 'resturantes.id', '=', 'resturant_branches.resturant_id')
                    ->where('favourites.user_id', $user->id)
                    ->select('resturantes.id as resturant_id', 'favourites.meal_id', 'meals.image', 'resturant_branches.title_' . $this->lang_code . ' as branch', 'meals.title_' . $this->lang_code . ' as 
           meal', 'resturantes.title_' . $this->lang_code . ' as resturant', 'meals.price', 'favourites.resturant_branch_id')
                    ->paginate($this->limit);

            return _api_json(User::transformCollection($favourites, 'Favourites'));
        } catch (\Exception $e) {
            $message = ['message' => _lang('app.error_occured')];
            return _api_json($favourites, $message, 422);
        }
    }

    public function myOrders(Request $request) {
        /*
          0 => new order
          1 => preparing order
          2 => delivering order
          3 => completed order
          4 => refused order
         */
        try {
            $validator = Validator::make($request->all(), $this->orders_rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return _api_json('', ['errors' => $errors], 422);
            }
            $orders = array();
            $user = $this->auth_user();

            $orders = Order::withTrashed();
            $orders->where('orders.user_id', $user->id);
            if ($request->type == 1) {
                $orders->whereIn('orders.status', [0, 1, 2,7]);
            } else {
                $orders->whereIn('orders.status', [3, 4]);
            }
            $orders->join('resturantes', 'orders.resturant_id', '=', 'resturantes.id');
            $orders->join('resturant_branches', 'resturant_branches.id', '=', 'orders.resturant_branch_id');
            $orders->join('cities', 'resturant_branches.region_id', '=', 'cities.id');
            $orders->select('orders.*', 'resturantes.title_' . $this->lang_code . ' as resturant', 'resturantes.image', 'cities.title_' . $this->lang_code . ' as region');
            $orders->orderBy('orders.date', 'desc');
            $orders = $orders->paginate($this->limit);


            $orders = Order::transformCollection($orders);

            return _api_json($orders);
        } catch (\Exception $e) {
            dd($e);
            $message = ['message' => _lang('app.error_occured')];
            return _api_json(array(), $message, 422);
        }
    }

}
