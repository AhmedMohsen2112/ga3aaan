<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\BackendController;
use App\Models\Setting;

class SettingsController extends BackendController {

    private $rules = array(
        'title_ar' => 'required', 'title_en' => 'required',
        'address_ar' => 'required', 'address_en' => 'required',
        'android_url' => 'required|url', 'ios_url' => 'required|url',
        'email' => 'required|email', 'phone' => 'required|numeric',
        'about_us_ar' => 'required', 'about_us_en' => 'required',
        'usage_conditions_ar' => 'required', 'usage_conditions_en' => 'required',
        'terms_conditions_ar' => 'required', 'terms_conditions_en' => 'required',
    );

    public function index() {
        $settings = Setting::get();
        $settings[0]->social_media = json_decode($settings[0]->social_media);
        $settings[0]->contact_person_social_media = json_decode($settings[0]->contact_person_social_media);
        $this->data['settings'] = $settings[0];
        return $this->_view('settings/index', 'backend');
    }

    public function update(Request $request, $id) {
        $Setting = Setting::find($id);
        //dd($request->all());
        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        } else {
            $Setting->title_ar = $request->input('title_ar');
            $Setting->title_en = $request->input('title_en');
            $Setting->address_ar = $request->input('address_ar');
            $Setting->address_en = $request->input('address_en');
            $Setting->phone = $request->input('phone');
            $Setting->email = $request->input('email');
            $Setting->android_url = $request->input('android_url');
            $Setting->ios_url = $request->input('ios_url');
            $Setting->about_us_ar = $request->input('about_us_ar');
            $Setting->about_us_en = $request->input('about_us_en');
            $Setting->usage_conditions_ar = $request->input('usage_conditions_ar');
            $Setting->usage_conditions_en = $request->input('usage_conditions_en');
            $Setting->terms_conditions_ar = $request->input('terms_conditions_ar');
            $Setting->terms_conditions_en = $request->input('terms_conditions_en');
            $Setting->social_media = json_encode($request->input('social_media'));

            try {

                $Setting->save();
                return _json('success', _lang('app.updated_successfully'));
            } catch (\Exception $ex) {
                return _json('error', _lang('app.error_is_occured'));
            }
        }
    }

}
