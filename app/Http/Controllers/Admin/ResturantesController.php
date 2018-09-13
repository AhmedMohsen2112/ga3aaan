<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;
use App\Models\Resturant;
use App\Models\PaymentMethod;
use App\Models\Cuisine;
use App\Models\Category;
use App\Models\Admin;
use Validator;
use DB;
use Str;

class ResturantesController extends BackendController {

    private $rules = array(
        'username' => 'required|unique:admins',
        'password' => 'required',
        'email' => 'required|email|unique:admins',
        'phone' => 'required|numeric|unique:admins',
        'title_ar' => 'required|unique:resturantes',
        'title_en' => 'required|unique:resturantes',
        'image' => 'required|image|mimes:gif,png,jpeg|max:1000',
        'delivery_time' => 'required|numeric',
        'minimum_charge' => 'required|numeric',
        'payment_methods' => 'required',
        'service_charge' => 'required|numeric',
        'vat' => 'required|numeric',
        'category' => 'required|numeric',
        'cuisines' => 'required',
        'commission' => 'required|numeric',
        'working_hours.Sat.from' => 'required','working_hours.Sat.to' => 'required','working_hours.Sun.from' => 'required','working_hours.Sun.to' => 'required',
        'working_hours.Mon.from' => 'required','working_hours.Mon.to' => 'required','working_hours.Tue.from' => 'required','working_hours.Tue.to' => 'required',
        'working_hours.Wed.from' => 'required','working_hours.Wed.to' => 'required','working_hours.Thu.from' => 'required','working_hours.Thu.to' => 'required',
        'working_hours.Fri.from' => 'required','working_hours.Fri.to' => 'required'
        
    );
    private $rules_messages = array();

    public function __construct() {
        parent::__construct();
        $this->middleware('CheckPermission:resturantes,open', ['only' => ['index']]);
        $this->middleware('CheckPermission:resturantes,add', ['only' => ['store']]);
        $this->middleware('CheckPermission:resturantes,edit', ['only' => ['show', 'update']]);
        $this->middleware('CheckPermission:resturantes,delete', ['only' => ['delete']]);
        $this->rules_messages = array(
            'required' => _lang('app.this_field_is_required')
        );
        
        $this->data['week_days']= array_chunk(Resturant::$week_days, 2);
       /// dd($this->data['week_days']);
        
    }

    public function index() {
        return $this->_view('resturantes/index', 'backend');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $this->data['categories'] = Category::where('active', true)
                ->select('id', 'title_' . $this->lang_code . ' as title')
                ->orderBy('this_order')
                ->get();
        $this->data['cuisines'] = Cuisine::where('active', true)
                ->select('id', 'title_' . $this->lang_code . ' as title')
                ->orderBy('this_order')
                ->get();
        $this->data['payment_methods'] = PaymentMethod::select('id', 'title_' . $this->lang_code . ' as title')
                ->get();
        return $this->_view('resturantes/create', 'backend');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //dd($request->all());
        
        $validator = Validator::make($request->all(), $this->rules, $this->rules_messages);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        }
        DB::beginTransaction();
        try {

            $resturant_admin = new Admin;
            $resturant_admin->username = $request->username;
            $resturant_admin->email = $request->email;
            $resturant_admin->phone = $request->phone;
            $resturant_admin->active = $request->user_active;
            $resturant_admin->password = bcrypt($request->password);
            $resturant_admin->type = 2;
            $resturant_admin->group_id = 2;
            $resturant_admin->save();


            $resturant = new Resturant;

            $resturant->title_ar = $request->title_ar;
            $resturant->title_en = $request->title_en;
            $resturant->slug = str_slug($resturant->title_en);
            $resturant->active = $request->active;
            $resturant->delivery_time = $request->delivery_time;
            $resturant->minimum_charge = $request->minimum_charge;
            $resturant->service_charge = $request->service_charge;
            $resturant->vat = $request->vat;
            $resturant->category_id = $request->category;
            $resturant->commission = $request->commission;
            $resturant->working_hours = json_encode($request->working_hours);
            $resturant->is_famous = $request->is_famous;
            $resturant->admin_id = $resturant_admin->id;
            if ($request->options) {
                $resturant->options = $request->options;
            }
            $resturant->image =$this->_upload($request->file('image'), 'resturantes', true, '\App\Models\Resturant');
            

            $resturant->save();

            for ($i = 0; $i < count($request->payment_methods); $i++) {
                $payment_method = PaymentMethod::where('id', $request->payment_methods[$i])->where('active', true)->first();
                if ($payment_method) {

                    $resturant->payment_methods()->attach($payment_method);
                }
            }

            for ($i = 0; $i < count($request->cuisines); $i++) {
                $cuisine = Cuisine::where('id', $request->cuisines[$i])->where('active', true)->first();
                if ($cuisine) {
                    $resturant->cuisines()->attach($cuisine);
                }
            }

            DB::commit();
            return _json('success', _lang('app.added_successfully'));
        } catch (\Exception $e) {
            DB::rollback();
            return _json('error', _lang('app.error_is_occured'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $find = Resturant::find($id);
        if ($find) {
            return $this->_view('resturantes/show', 'backend');
        } else {
            session()->flash('message', _lang('app.not_found'));
            return redirect('resturants.index');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        $find = Resturant::find($id);
        if ($find) {

            $resturant_admin = Admin::where('id', $find->admin_id)->first();
            $find->working_hours = json_decode($find->working_hours, true);
            $this->data['resturant'] = $find;
            $this->data['admin'] = $resturant_admin;
            $this->data['categories'] = Category::where('active', true)
                    ->select('id', 'title_' . $this->lang_code . ' as title')
                    ->orderBy('this_order')
                    ->get();
            $this->data['resturant_cuisines'] = $find->cuisines()->pluck('cuisine_id')->toArray();
            $this->data['cuisines'] = Cuisine::where('active', true)
                    ->select('id', 'title_' . $this->lang_code . ' as title')
                    ->orderBy('this_order')
                    ->get();
            $this->data['resturant_payment_methods'] = $find->payment_methods()->pluck('payment_method_id')->toArray();
            $this->data['payment_methods'] = PaymentMethod::select('id', 'title_' . $this->lang_code . ' as title')
                    ->get();
            return $this->_view('resturantes/edit', 'backend');
        } else {
            session()->flash('message', _lang('app.not_found'));
            return redirect('resturants.index');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $resturant = Resturant::find($id);
        if (!$resturant) {
            return _json('error', _lang('app.error_is_occured'));
        }

        unset($this->rules['image']);
        $this->rules['title_ar'] = 'required|unique:resturantes,title_ar,' . $resturant->id;
        $this->rules['title_en'] = 'required|unique:resturantes,title_en,' . $resturant->id;
        $this->rules['username'] = 'required|unique:admins,username,' . $resturant->admin_id;
        $this->rules['email'] = 'required|unique:admins,email,' . $resturant->admin_id;
        $this->rules['phone'] = 'required|unique:admins,phone,' . $resturant->admin_id;
        unset($this->rules['password']);

        $validator = Validator::make($request->all(), $this->rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        }
        DB::beginTransaction();
        try {

            $resturant->title_ar = $request->title_ar;
            $resturant->title_en = $request->title_en;
            $resturant->slug = str_slug($resturant->title_en);
            $resturant->active = $request->active;
            if ($request->file('image')) {
                $old_image = $resturant->image;
                $this->deleteUploaded('resturantes', $old_image, '\App\Models\Resturant');
                $resturant->image = $this->_upload($request->file('image'), 'resturantes', true, '\App\Models\Resturant');
            }
            $resturant->delivery_time = $request->delivery_time;
            $resturant->minimum_charge = $request->minimum_charge;
            $resturant->service_charge = $request->service_charge;
            $resturant->vat = $request->vat;
            $resturant->category_id = $request->category;
            $resturant->commission = $request->commission;
            $resturant->working_hours = json_encode($request->working_hours);
            $resturant->is_famous = $request->is_famous;
            if ($request->options || $request->options == 0) {
                $resturant->options = $request->options;
            }

            $resturant->save();


            $resturant_admin = Admin::where('id', $resturant->admin_id)->first();
            $resturant_admin->username = $request->username;
            $resturant_admin->email = $request->email;
            $resturant_admin->phone = $request->phone;
            $resturant_admin->active = $request->user_active;
            if ($request->password) {
                $resturant_admin->password = bcrypt($request->password);
            }

            $resturant_admin->save();

            $resturant->payment_methods()->detach();
            $resturant->cuisines()->detach();

            for ($i = 0; $i < count($request->payment_methods); $i++) {
                $payment_method = PaymentMethod::where('id', $request->payment_methods[$i])->where('active', true)->first();
                if ($payment_method) {
                    $resturant->payment_methods()->attach($payment_method);
                }
            }

            for ($i = 0; $i < count($request->cuisines); $i++) {
                $cuisine = Cuisine::where('id', $request->cuisines[$i])->where('active', true)->first();
                if ($cuisine) {
                    $resturant->cuisines()->attach($cuisine);
                }
            }

            DB::commit();
            return _json('success', _lang('app.updated_successfully'));
        } catch (\Exception $e) {
            DB::rollback();
            return _json('error', _lang('app.error_is_occured'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
         $resturant = Resturant::find($id);
          if (!$resturant) {
          return _json('error', _lang('app.error_is_occured'), 400);
          }
          try {
          $old_image = $resturant->image;
          $resturant->delete();
          $this->deleteUploaded('resturantes', $old_image, '\App\Models\Resturant');
          return _json('success', _lang('app.deleted_successfully'));
          } catch (\Exception $ex) {
          if ($ex->getCode() == 23000) {
          return _json('error', _lang('app.this_record_can_not_be_deleted_for_linking_to_other_records'), 400);
          } else {
          return _json('error', _lang('app.error_is_occured'), 400);
          }
          } 
    }

    public function data(Request $request) {
        $Resturantes = Resturant::select([
                    'id', "title_" . $this->lang_code . " as resturant", "image", 'active'
        ]);
        return \Datatables::eloquent($Resturantes)
                        ->addColumn('options', function ($item) {

                            $back = "";
                            if (\Permissions::check('resturantes', 'edit') || \Permissions::check('resturantes', 'delete')) {
                                $back .= '<div class="btn-group">';
                                $back .= ' <button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> options';
                                $back .= '<i class="fa fa-angle-down"></i>';
                                $back .= '</button>';
                                $back .= '<ul class = "dropdown-menu" role = "menu">';

                                


                                if (\Permissions::check('resturantes', 'edit')) {
                                    $back .= '<li>';
                                    $back .= '<a href="' . route('resturantes.edit', $item->id) . '" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.edit');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }

                                if (\Permissions::check('resturantes', 'delete')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" data-toggle="confirmation" onclick = "Resturantes.delete(this);return false;" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.delete');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }
                                if (\Permissions::check('resturant_branches', 'view')) {
                                    $back .= '<li>';
                                    $back .= '<a href="' . route('resturant_branches.index') . '?resturant=' . $item->id . '"  class="data-box">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.branches');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }
                                if (\Permissions::check('menu_sections', 'view')) {
                                    $back .= '<li>';
                                    $back .= '<a href="' . route('menu_sections.index') . '?resturant=' . $item->id . '"  class="data-box">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.Menu');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }
                                if (\Permissions::check('choices', 'open')) {
                                    $back .= '<li>';
                                    $back .= '<a href="' . route('choices.index') . '?resturant=' . $item->id . '"  class="data-box">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.choices');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }
                                $back .= '</ul>';
                                $back .= ' </div>';
                            }
                            return $back;
                        })
                        ->editColumn('image', function ($item) {
                            $back = '<img src="' . url('public/uploads/resturantes/' . $item->image) . '" style="height:64px;width:64px;"/>';
                            return $back;
                        })
                        ->editColumn('active', function ($item) {
                            if ($item->active == 1) {
                                $message = _lang('app.active');
                                $class = 'label-success';
                            } else {
                                $message = _lang('app.not_active');
                                $class = 'label-danger';
                            }
                            $back = '<span class="label label-sm ' . $class . '">' . $message . '</span>';
                            return $back;
                        })
                        ->escapeColumns([])
                        ->make(true);
    }

}
