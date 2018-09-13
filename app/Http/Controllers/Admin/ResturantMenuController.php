<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;
use App\Models\MenuSection;
use App\Models\MenuSectionTopping;
use App\Models\Resturant;
use App\Models\Topping;
use DB;
use Validator;
use Session;

class ResturantMenuController extends BackendController {

    public function __construct() {
        parent::__construct();
        $this->middleware('CheckPermission:resturant_menu,open', ['only' => ['index']]);

    }

    public function index(Request $request) {
        return $this->_view('resturant_menu/index', 'backend');
    }



    public function data(Request $request) {
        $resturant_menu = MenuSection::where('resturant_id', $this->User->resturant->id)
                ->select([
            'id', "title_" . $this->lang_code . " as title", "this_order", 'active'
        ]);

        return \Datatables::eloquent($resturant_menu)
                        ->addColumn('options', function ($item) {

                            $back = "";
                            if (\Permissions::check('resturant_meals', 'open')) {

                                $back .= '<a class="btn btn-info" href="' . url('admin/resturant_meals?menu_section=' . $item->id ) . '">';
                                $back .= _lang('app.meals');
                                $back .= '</a>';
                            }
                           
                            return $back;
                        })
                        ->escapeColumns([])
                        ->make(true);
    }

 

}
