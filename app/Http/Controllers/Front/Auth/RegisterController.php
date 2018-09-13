<?php

namespace App\Http\Controllers\Front\Auth;

use App\Http\Controllers\FrontController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use App\Models\User;
use Session;
use Socialite;

class RegisterController extends FrontController
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/activation';

    private $rules = array(
        'first_name' => 'required',
        'last_name' => 'required',
        'email' => 'required|email|unique:users',
        'mobile' => 'required|unique:users|numeric',
        'password' => 'required',
        'confirm_password' => 'required|same:password',
        'conditions' => 'required'
    );
    private $confirm_rules = array(
        'activation' => 'required',
    );

    

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       parent::__construct();
       $this->middleware('guest');
    }

   

    public function showRegistrationForm() {
        $this->data['page_title'] = _lang('app.registeration');
        return $this->_view('auth/register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules);
        if ($validator->fails()) {
            if ($request->ajax()) {

                $errors = $validator->errors()->toArray();
                return response()->json([
                            'type' => 'error',
                            'errors' => $errors
                ]);
            } else {
                return redirect()->back()->withErrors($validator->errors()->toArray());
            }
        }
        

        session()->put('ga3an_data', $request->all());
        session()->put('ga3an_activation_code', 1234);
        
        
        if ($request->ajax()) {
             return _json('success',route('activation'));
        }
        return  redirect('activation');
    }

    public function showEditMobileForm()
    {
        $this->data['page_title'] = _lang('app.edit_mobile_number');
        return $this->_view('auth.edit_mobile');
    }

    public function EditPhone(Request $request)
    {
         try {

            $validator = Validator::make($request->all(),['mobile' => 'required']);
            if ($validator->fails()) {
                if ($request->ajax()) {

                    $errors = $validator->errors()->toArray();
                    return response()->json([
                                'type' => 'error',
                                'errors' => $errors
                    ]);
                } else {
                    return redirect()->back()->withErrors($validator->errors()->toArray());
                }
            }

            $user_data = session()->get('ga3an_data');
            $user_data['mobile'] = $request->mobile;

            session()->put('ga3an_data',$user_data);
            session()->put('ga3an_activation_code',4567);
            
            if ($request->ajax()) {
            return _json('success',route('activation'));
            }
            return  redirect('activation');

            
        } catch (\Exception $e) {
            if ($request->ajax()) {
             return _json('false',_lang('app.error_has_occured'));
           }
           session()->flash('message',_lang('app.error_has_occured'));
           return redirect()->back();
        }
    }


    public function showActivationForm()
    {
        if (!session()->get('ga3an_activation_code')) {
            return redirect()->route('/');
        }
        $this->data['page_title'] = _lang('app.activation_code');
        return $this->_view('auth.activate');
    }

    public function activate_user(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), $this->confirm_rules);
            if ($validator->fails()) {
                if ($request->ajax()) {

                    $errors = $validator->errors()->toArray();
                    return response()->json([
                                'type' => 'error',
                                'errors' => $errors
                    ]);
                } else {
                    return redirect()->back()->withErrors($validator->errors()->toArray());
                }
            }
            
            $activation_code = session()->get('ga3an_activation_code');

            $entered_activation_code = implode("", array_reverse($request->activation));

            if ($activation_code == $entered_activation_code) {
                $user_data = session()->get('ga3an_data');
                event(new Registered($user = $this->create($user_data)));
                $this->guard('web')->login($user);
                session()->forget('ga3an_activation_code');
                if ($request->ajax()) {
                    return _json('success',route('home'));
                }
                return redirect('home');
            }else{
                if ($request->ajax()) {
                 return _json('false',_lang('app.this_code_is_incorrect'));
               }
               session()->flash('message',_lang('app.this_code_is_incorrect'));
               return redirect()->back();
            }  

        } catch (\Exception $e) {
            if ($request->ajax()) {
             return _json('false',_lang('app.error_has_occured'));
           }
           session()->flash('message',_lang('app.error_has_occured'));
           return redirect()->back();
        }
    }

    protected function guard()
    {
        return Auth::guard('web');
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {

        $user = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'password' => bcrypt($data['password']),
            'active' => false
        ];
        if (isset($data['sms_notify'])) {
            $user['sms_notify'] = $data['sms_notify'];
        }
        if (isset($data['email_notify'])) {
            $user['email_notify'] = $data['email_notify'];
        }

        return User::create($user);
    }

    public function showCompleteRegistrationForm() {
        return $this->_view('auth/complete_register');
    }

     /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        $user = Socialite::driver('facebook')->user();

        $find = User::where('email',$user->email)->first();
        if ($find) {
            session()->flash('msg',_lang('app.this_email_is_already_exist'));
            return redirect()->route('login');
        }

        $fullname = explode(" ",$user->name);
        session()->put('ga3aaan_first_name', $fullname[0]);
        session()->put('ga3aaan_last_name', $fullname[1]);
        session()->put('ga3aaan_email', $user->email);
       
        return redirect()->route('complete_register');
    }

}
