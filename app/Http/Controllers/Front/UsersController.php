<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\FrontController;
use App\Models\User;
use App\Models\Meal;
use App\Models\Favourite;
use Validator;

class UsersController extends FrontController {

    private $rules = array(
        'first_name' => 'required',
        'last_name' => 'required',
    );

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
    }

    public function profile() {
        $this->data['page_title'] = _lang('app.profile');
        try {

            return $this->_view('users.profile');
        } catch (\Exception $e) {

            session()->flash('msg', _lang('app.error_is_occured_try_again_later'));
            return redirect()->back();
        }
    }

    public function editProfile() {
        $this->data['page_title'] = _lang('app.edit_profile');
        try {
            return $this->_view('users.edit_profile');
        } catch (\Exception $e) {
            session()->flash('msg', _lang('app.error_is_occured_try_again_later'));
            return redirect()->back();
        }
    }

    public function updateProfile(Request $request) {
        try {

            $User = $this->User;

            $this->rules['email'] = "required|email|unique:users,email,$User->id";
            $this->rules['mobile'] = "required|unique:users,mobile,$User->id";

            if ($request->file('user_image')) {
                $this->rules['user_image'] = "required|image|mimes:gif,png,jpeg|max:1000";
            }
            if ($request->input('password')) {
                $this->rules['password'] = "required";
                $this->rules['confirm_password'] = "required|same:password";
            }
            $validator = Validator::make($request->all(), $this->rules);
            if ($validator->fails()) {

                if ($request->ajax()) {
                    $errors = $validator->errors()->toArray();
                    return _json('error', $errors);
                } else {
                    return redirect()->back()->withInput($request->only('email'))->withErrors($validator->errors()->toArray());
                }
            }

            $User->first_name = $request->input('first_name');
            $User->last_name = $request->input('last_name');
            $User->mobile = $request->input('mobile');
            $User->email = $request->input('email');

            if ($request->input('password')) {
                $User->password = bcrypt($request->input('password'));
            }
            if ($request->file('user_image')) {
                $file = url("public/uploads/users/$User->user_image");
                if (!is_dir($file)) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }

                $User->user_image = $this->_upload($request->file('user_image'), 'users');
            }
            $User->save();
            if ($request->ajax()) {
                return _json('success', _lang('app.updated_successfully'));
            }
            session()->flash('msg', _lang('app.updated_successfully'));
            return redirect()->back();
        } catch (\Exception $e) {

            session()->flash('msg', _lang('app.error_is_occured_try_again_later'));
            return redirect()->back();
        }
    }

    public function addDeleteFavourite(Request $request) {

        //dd($request->all());
        try {


            $favorite = Favourite::where('meal_id', $request->meal)
                    ->where('resturant_branch_id', $request->branch)
                    ->where('user_id', $this->User->id)
                    ->first();
            if ($favorite) {
                $favorite->delete();
                $message = false;
            } else {
                $favourite = new Favourite;
                $favourite->meal_id = $request->meal;
                $favourite->resturant_branch_id = $request->branch;
                $favourite->user_id = $this->User->id;
                $favourite->save();
                $message = true;
            }
       
            if ($request->ajax()) {
                return _json('success', $message);
            } else {
                return redirect()->back()->withInput($request->all())->with(['successMessage' => $message]);
            }
        } catch (\Exception $e) {
            if ($request->ajax) {
                return _json('error', _lang('app.error_is_occured'));
            } else {
                return redirect()->back()->withInput($request->all())->with(['errorMessage' => _lang('app.error_is_occured')]);
            }
        }
    }

    public function favourites() {
        $this->data['page_title'] = _lang('app.favourites');
        $user = $this->User;
        $favourites = Favourite::join('resturant_branches', 'favourites.resturant_branch_id', '=', 'resturant_branches.id')
                ->join('meals', 'favourites.meal_id', '=', 'meals.id')
                ->join('resturantes', 'resturantes.id', '=', 'resturant_branches.resturant_id')
                ->where('favourites.user_id', $user->id)
                ->select(['resturantes.id as resturant_id', 'meals.id as meal_id','resturant_branches.slug as resturant_slug', 'meals.image',
                     "resturant_branches.title_$this->lang_code as branch","resturant_branches.id as branch_id",
                    'meals.title_' . $this->lang_code . ' as meal', 'resturantes.title_' . $this->lang_code . ' as resturant', 'meals.price'])
                ->paginate($this->limit);
        $this->data['favourites'] = $favourites;

        return $this->_view('users.favourites');
    }

    

}
