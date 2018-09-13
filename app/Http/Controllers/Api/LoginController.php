<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use App\Helpers\AUTHORIZATION;
use App\Models\User;

class LoginController extends ApiController {

    private $rules = array(
        'username' => 'required',
        'password' => 'required',
        'device_token' => 'required',
        'device_type' => 'required',
    );

    public function login(Request $request) {
        $validator = Validator::make($request->all(), $this->rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _api_json(new \stdClass(), ['errors' => $errors], 422);
        } else {

            $credentials_arr = $request->only('username', 'password');

            if ($this->checkIFEmail($request->only('username'))) {
                $credentials['email'] = $credentials_arr['username'];
                $credentials['password'] = $credentials_arr['password'];
            } else {
                $credentials['mobile'] = $credentials_arr['username'];
                $credentials['password'] = $credentials_arr['password'];
            }
            $credentials['active'] = true;
            if ($user = $this->auth_check($credentials)) {
                $token = new \stdClass();
                $token->id = $user->id;
                $token->expire = strtotime('+' . $this->expire_no . $this->expire_type);
                $expire_in_seconds = $token->expire;
                $this->update_token($request->input('device_token'), $request->input('device_type'), $user->id);
                return _api_json(User::transform($user), ['message' => _lang('app.login_done_successfully'), 'token' => AUTHORIZATION::generateToken($token), 'expire' => $expire_in_seconds]);
            }
           return _api_json(new \stdClass(), ['message' => _lang('app.invalid_credentials')] , 422);
        }
    }

    private function checkIFEmail($request) {
        $validator = Validator::make($request, ['username' => 'email']);
        if ($validator->fails()) {
            return false;
        }
        return true;
    }

    private function auth_check($credentials) {
        $where_array = array();
        foreach ($credentials as $key => $value) {
            if ($key == 'password') {
                continue;
            }
            $where_array[] = array($key, '=', $value);
        }
        $find = User::where($where_array)->get();

        if ($find->count()) {
            if (password_verify($credentials['password'], $find[0]->password)) {
                return $find[0];
            }
        }
        return false;
    }

}
