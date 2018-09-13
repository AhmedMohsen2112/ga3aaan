<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\BackendController;
use App\Models\AdminNotification;
use App\Notifications\GeneralNotification;
use App\Helpers\Fcm;
use Notification;
use App\Models\User;

class NotificationsController extends BackendController {

    private $rules = array(
        'title' => 'required', 'message' => 'required',
    );

    public function index() {
        return $this->_view('notifications/index', 'backend');
    }

    public function store(Request $request) {

        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        } else {
            try {
              
                $title = $request->input('title');
                $body['message'] = $request->input('message');
                $body['type'] = 2;

                $notification = array('title' => $title, 'body' => $body);
                $Fcm = new Fcm;
                
                $token = '/topics/ga3an_and';
                $Fcm->send($token, $notification, 'and');
              
                $token = '/topics/ga3an_ios';
                $Fcm->send($token, $notification, 'ios');
                
               // $token="de9GvwkpMJ8:APA91bFPjodK2XHRFdSUSsTlfcfBNkDkknWfxnqjOLs2Oq9I4oBzwpnDOYx6cBYzhzHgMadIHebClS49fhQiw8bKVHiknVvmT2ZyXR2EK6S0iv1hj3jstxRaIY4MMrdgfIo3fveVvmSw";
                    
                    
                return _json('success', _lang('app.sent_successfully'));
            } catch (Exception $ex) {
                return _json('error', _lang('app.error_is_occured'), 400);
            }
        }
    }

}
