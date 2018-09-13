<?php

namespace App\Http\Controllers\Front\Auth;

use App\Http\Controllers\FrontController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;
use Validator;

class LoginController extends FrontController {

    use AuthenticatesUsers;

    private $rules = array(
        'username' => 'required',
        'password' => 'required'
    );

    public function __construct() {
        parent::__construct();
        $this->middleware('guest', ['except' => ['logout']]);
    }

    public function showLoginForm() {
        $this->data['page_title'] = _lang('app.login');
        return $this->_view('auth/login');
    }

    public function login(Request $request) {

        $validator = Validator::make($request->all(), $this->rules);
        if ($validator->fails()) {
            if ($request->ajax()) {

                $errors = $validator->errors()->toArray();
                return response()->json([
                            'type' => 'error',
                            'errors' => $errors
                ]);
            } else {
                return redirect()->back()->withInput($request->only('email'))->withErrors($validator->errors()->toArray());
            }
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

            if (Auth::guard('web')->attempt($credentials, $request->remember)) {
                if ($request->return) {
                    $url = url(base64_decode($request->return));
                } else {
                    $url = _url('');
                }
                if ($request->ajax()) {

                    return _json('success', $url);
                } else {
                    return redirect($url);
                }
            } else {
                $msg = _lang('messages.invalid_email_or_password');
                if ($request->ajax()) {
                    return _json('error', $msg);
                } else {
                    return redirect()->back()->withInput($request->only('email', 'remember'))->withErrors(['msg' => $msg]);
                }
            }
        }
    }

    public function logout() {
        Auth::guard('web')->logout();
        return redirect('/login');
    }

    private function checkIFEmail($request) {
        $validator = Validator::make($request, ['username' => 'email']);
        if ($validator->fails()) {
            return false;
        }
        return true;
    }

}
