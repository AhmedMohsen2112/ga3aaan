<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Auth;
use Validator;
use App\Models\Admin;
use App\Models\Group;

//use My;

class LoginController extends Controller {

    use AuthenticatesUsers;

    private $rules = array(
        'username' => 'required',
        'password' => 'required'
    );

    public function __construct() {
        $this->middleware('guest:admin', ['except' => ['logout']]);
    }

    public function showLoginForm() {

        return view('main_content/backend/login');
    }

    public function login(Request $request) {

        $validator = Validator::make($request->all(), $this->rules);
        if ($validator->fails()) {
            if ($request->ajax()) {
                $errors = $validator->errors()->toArray();
                return _json('error', $errors);
            } else {
                return redirect()->back()->withInput($request->only('username', 'remember'))->withErrors($validator->errors()->toArray());
            }
        } else {
            $username = $request->input('username');
            $password = $request->input('password');
            $Admin = $this->checkAuth($username);
            $is_logged_in = false;
            if ($Admin) {
                if (password_verify($password, $Admin->password)) {
                    $is_logged_in = true;
                }
            }
            if ($is_logged_in) {
                Auth::guard('admin')->login($Admin);
                if ($request->ajax()) {
                    return _json('success',  route('admin.dashboard'));
                } else {
                    return redirect()->intended( route('admin.dashboard'));
                }
            } else {
                $msg = _lang('messages.invalid_credentials');
                if ($request->ajax()) {
                    return _json('error', $msg);
                } else {
                    return redirect()->back()->withInput($request->only('username', 'remember'))->withErrors(['msg' => $msg]);
                }
            }
        }
    }

    public function login2(Request $request) {

        // Validate the form data

        $rules = array(
            'username' => 'required',
            'password' => 'required'
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return response()->json([
                        'type' => 'error',
                        'errors' => $errors
            ]);
        } else {
            if (Auth::guard('admin')->validate(['username' => $request->username, 'password' => $request->password])) {
                $user = Auth::guard('admin')->getLastAttempted();
                $group = Group::where('active', 1)->find($user->group_id);
                if ($group != null) {
                    Auth::guard('admin')->login($user, $request->has('remember'));
                    return response()->json([
                                'type' => 'success',
                                'url' => route('admin.dashboard')
                    ]);
                } else {
                    Auth::guard('admin')->logout();
                    return response()->json([
                                'type' => 'error',
                                'message' => _lang('app.access_denied')
                    ]);
                }
            } else {

                return response()->json([
                            'type' => 'error',
                            'message' => _lang('app.invalid_username_or_password')
                ]);
            }
        }
    }

    private function checkAuth($username) {
       $Admin=Admin::join('groups','groups.id','=','admins.group_id')
               ->where('groups.active',1)
               ->where('admins.active',1)
               ->where('admins.username',$username)
               ->select('admins.*')
               ->first();
        if ($Admin) {
            return $Admin;
        }
        return false;
    }

    public function logout() {
        Auth::guard('admin')->logout();
        return redirect('/admin/login');
    }

}
