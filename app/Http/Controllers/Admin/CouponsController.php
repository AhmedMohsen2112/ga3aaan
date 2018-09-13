<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;
use App\Models\Coupon;
use App\Models\Resturant;
use Validator;


class CouponsController extends BackendController
{
     private $rules = array(
        'coupon' => 'required',
        'available_until' => 'required',
        'discount' => 'required',
    );

    public function __construct() {

        parent::__construct();
        $this->middleware('CheckPermission:coupons,open', ['only' => ['index']]);
        $this->middleware('CheckPermission:coupons,add', ['only' => ['store']]);
        $this->middleware('CheckPermission:coupons,delete', ['only' => ['delete']]);

    }

    public function index() {
        $resturantes = Resturant::where('active',true)->select('id','title_'.$this->lang_code.' as title')->get();
        $this->data['resturantes'] = $resturantes;
        return $this->_view('coupons/index', 'backend');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        if ($request->input('resturant_id')) {
            $this->rules['resturant_branch'] = 'required';
        }
        $validator = Validator::make($request->all(), $this->rules);
        
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
          
        } else {
            $errors = $this->inputs_check('\App\Models\Coupon', array(
                'coupon' => $request->input('coupon'),
            ));
            if (!empty($errors)) {
            	return _json('error', $errors);
            }

            $Coupon = new Coupon;

            $Coupon->coupon   = $request->input('coupon');
            if ($request->input('resturant_id')) {
                $Coupon->resturant_id   = $request->input('resturant_id');
            }
            if ($request->input('resturant_branch')) {
                $Coupon->resturant_branch_id   = $request->input('resturant_branch');
            }
            $Coupon->available_until   = $request->available_until;
            $Coupon->discount   = $request->discount;

            if ($Coupon->save()) {
               return _json('success', _lang('app.added_successfully'));
            } else {
               return _json('error', _lang('app.error_is_occured'));
            }
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $Coupon = Coupon::find($id);
        if (!$Coupon) {
        	return _json('error', _lang('app.error_is_occured'),400);
        }
        if ($Coupon->delete()) {
            return _json('success', _lang('app.deleted_successfully'));
        } else {
        	return _json('error', _lang('app.error_is_occured'));
        }
    }


    public function status($id)
    {
        $data = array();
        $Coupon = Coupon::find($id);

        if ($Coupon != null) {
            if ($Coupon->active == true) {
                $Coupon->active = false;
                $data['status'] = false;
            }
            else{
                 $Coupon->active = true;
                 $data['status'] = true;
            }
            $Coupon->save();
        
            return $data;
           
        }else {
            return $data;
        }

    }

    public function data(Request $request) {

        $coupons = Coupon::leftJoin('resturantes','resturantes.id','=','coupons.resturant_id')
        ->leftJoin('resturant_branches','resturant_branches.id','=','coupons.resturant_branch_id')
        ->select([
                    'coupons.id', "coupons.coupon", "coupons.available_until", 'coupons.discount','resturantes.title_'.$this->lang_code.' as resturant','resturant_branches.title_'.$this->lang_code.' as resturant_branch'
        ]);

        return \Datatables::eloquent($coupons)
                        ->addColumn('options', function ($item) {

                            $back = "";
                            if (\Permissions::check('coupons', 'edit') || \Permissions::check('coupons', 'delete')) {
                                $back .= '<div class="btn-group">';
                                $back .= ' <button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> options';
                                $back .= '<i class="fa fa-angle-down"></i>';
                                $back .= '</button>';
                                $back .= '<ul class = "dropdown-menu" role = "menu">';

                                if (\Permissions::check('coupons', 'delete')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" data-toggle="confirmation" onclick = "Coupons.delete(this);return false;" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.delete');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }
                                $back .= '</ul>';
                                $back .= ' </div>';
                            }
                            return $back;
                        }) 
                        ->editColumn('discount', function ($item) {
                            $back = $item->discount.'%';
                            return $back;
                        })
                        ->escapeColumns([])
                        ->make(true);
    }
}
