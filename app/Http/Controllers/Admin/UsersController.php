<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;
use App\Models\User;
use Validator;
use DB;

class UsersController extends BackendController {

    

    public function __construct() {

        parent::__construct();
        $this->middleware('CheckPermission:users,open');
        $this->middleware('CheckPermission:users,add', ['only' => ['store']]);
        $this->middleware('CheckPermission:users,edit', ['only' => ['show', 'update']]);
        $this->middleware('CheckPermission:users,delete', ['only' => ['delete']]);
    }

    public function index(Request $request) {

        return $this->_view('users/index', 'backend');
    }


     /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $User = User::find($id);
        if (!$User) {
            return _json('error', _lang('app.error_is_occured'), 400);
        }
        try {
            $old_image = $User->user_image;
            $User->delete();
            $file = public_path("uploads/users/$old_image");
            if (!is_dir($file)) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            return _json('success', _lang('app.deleted_successfully'));
        } catch (\Exception $ex) {
            if ($ex->getCode() == 23000) {
                return _json('error', _lang('app.this_record_can_not_be_deleted_for_linking_to_other_records'), 400);
            } else {
                return _json('error', _lang('app.error_is_occured'), 400);
            }
        }
     
    }

    public function status($id) {
        $data = array();
        $User = User::where('id', $id)->first();
        if ($User != null) {
            if ($User->active == true) {
                $User->active = false;
                $data['status'] = false;
            } else {
                $User->active = true;
                $data['status'] = true;
            }
            $User->save();

            return $data;
        } else {
            return $data;
        }
    }

    public function data() {
       
        $users = User::select(['id','first_name','last_name','email','mobile','user_image','active']);

        return \Datatables::eloquent($users)
                     ->addColumn('options', function ($item) {

                                        $back = "";
                                        if (\Permissions::check('users', 'delete')) {
                                            $back .= '<div class="btn-group">';
                                            $back .= ' <button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> options';
                                            $back .= '<i class="fa fa-angle-down"></i>';
                                            $back .= '</button>';
                                            $back .= '<ul class = "dropdown-menu" role = "menu">';
                                            
                                            if (\Permissions::check('users', 'delete')) {
                                                $back .= '<li>';
                                                $back .= '<a href="" data-toggle="confirmation" onclick = "Users.delete(this);return false;" data-id = "' . $item->id . '">';
                                                $back .= '<i class = "icon-docs"></i>' . _lang('app.delete');
                                                $back .= '</a>';
                                                $back .= '</li>';
                                            }
                                            $back .= '</ul>';
                                            $back .= ' </div>';
                                        }
                                        return $back;
                                    })
                            ->editColumn('active', function ($item) {
                                    if ($item->active == 1) {
                                        $message = _lang('app.active');
                                        $class = 'btn-info';
                                    } else {
                                        $message = _lang('app.not_active');
                                        $class = 'btn-danger';
                                    }
                                    $back = '<a class="btn ' . $class . '" onclick = "Users.status(this);return false;" data-id = "' . $item->id . '" data-status = "' . $item->active . '">' . $message . ' <a>';
                                    return $back;
                                })
                             ->editColumn('user_image', function ($item) {
                                        if ($item->user_image) {
                                            $back = '<img src="' . url('public/uploads/users/' . $item->user_image) . '" style="height:64px;width:64px;"/>';
                                        }
                                        else{
                                             $back = '<img src="' . url('public/uploads/users/default.png') . '" style="height:64px;width:64px;"/>';
                                        }
                                       
                                        return $back;
                                })
                               ->escapeColumns([])
                               ->make(true);

       
    }

}
