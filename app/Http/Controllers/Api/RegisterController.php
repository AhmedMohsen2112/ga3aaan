<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use App\Helpers\AUTHORIZATION;
use App\Models\User;
use DB;

class RegisterController extends ApiController {

    private $rules = array(
        'first_name' => 'required',
        'last_name' => 'required',
        'email' => 'required|email|unique:users',
        'mobile' => 'required|unique:users',
        'password' => 'required',
        'step' => 'required',
    );

    public function __construct() {
        parent::__construct();
    }

    protected function register(Request $request) {

        $validator = Validator::make($request->all(), $this->rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _api_json(new \stdClass(), ['errors' => $errors],422);
        } else {
            try {
                if ($request->step == 1) {
                    //$verifaction_code = Random(4);
                    $verifaction_code = 1234;
                    return _api_json( new \stdClass(), ['verifaction_code' => $verifaction_code]);
                }
                elseif($request->step == 2){
                    //dd('here');
                    $user = $this->create_user($request);
                    $token = new \stdClass();
                    $token->id = $user->id;
                    $token->expire = strtotime('+' . $this->expire_no . $this->expire_type);
                    $expire_in_seconds = $token->expire;
                    return _api_json( User::transform($user), ['token' => AUTHORIZATION::generateToken($token), 'expire' => $expire_in_seconds]);

                }
                else{
                   $message = _lang('app.error_is_occured');
                   return _api_json(new \stdClass(), ['message' => $message],422);
                }
               
            } catch (\Exception $e) {
                $message = _lang('app.error_is_occured');
                return _api_json(new \stdClass(), ['message' => $message],422);
            }
        }
    }

    public function resendActivationCode(Request $request)
    {
        try {
        $validator = Validator::make($request->all(), ['mobile' => 'required']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _api_json('', ['errors' => $errors],422);
        } 
        //$verifaction_code = Random(4);
        $verifaction_code = 1234;
        return _api_json('', ['verifaction_code' => $verifaction_code]);

            
        } catch (\Exception $e) {
            $message = _lang('app.error_is_occured');
            return _api_json('', ['message' => $message],422);
        }
        
    }

    private function create_user($request) {
        $User = new User;
        $User->first_name = $request->input('first_name');
        $User->last_name = $request->input('last_name');
        $User->mobile = $request->input('mobile');
        $User->email = $request->input('email');
        $User->password = bcrypt($request->input('password'));
        $User->sms_notify = $request->input('sms_notify');
        $User->email_notify = $request->input('email_notify');
        if ($request->user_image) {
            $User->user_image = img_decoder($request->input('user_image'), 'users');
        }
        $User->device_token = $request->input('device_token');
        $User->device_type = $request->input('device_type');
        $User->save();
        return $User;
    }

}
