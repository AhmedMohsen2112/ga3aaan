<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;
use App\Models\Resturant;
use App\Models\MenuSection;
use App\Models\Offer;
use Validator;
use DB;

class OffersController extends BackendController {

    private $rules = array(
        'resturant' => 'required',
        'available_until' => 'required',
        'type' => 'required',
        'image' => 'required|mimes:jpeg,png|max:2000',
        'this_order' => 'required'
    );

    public function __construct() {
        parent::__construct();
        $this->middleware('CheckPermission:offers,open', ['only' => ['index']]);
        $this->middleware('CheckPermission:offers,add', ['only' => ['store']]);
        $this->middleware('CheckPermission:offers,edit', ['only' => ['show', 'update']]);
        $this->middleware('CheckPermission:offers,delete', ['only' => ['delete']]);
    }

    public function index() {

        return $this->_view('offers/index', 'backend');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {

        $offers = Offer::where('available_until', '>', date('Y-m-d'))
                ->where('active', true)
                ->pluck('resturant_id')
                ->toArray();
        $this->data['resturantes'] = Resturant::where('active', true)
                ->select('id', 'title_' . $this->lang_code . ' as title')
                ->whereNotIn('id', $offers)
                ->get();

        return $this->_view('offers/create', 'backend');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        if ($request->type == 2 || $request->type == 3) {
            $this->rules['menu_sections'] = 'required';
        }
        if ($request->type != 4) {
            $this->rules['discount'] = 'required';
        }
        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        }
        DB::beginTransaction();
        try {

            $offer = new Offer;
            $offer->resturant_id = $request->resturant;
            $offer->available_until = $request->available_until;
            $offer->type = $request->type;
            $offer->active = $request->active;
            if ($request->discount) {
                $offer->discount = $request->discount;
            }
            $offer->this_order = $request->this_order;
            $offer->image = $this->_upload($request->file('image'), 'offers', true, '\App\Models\Offer');
            if ($request->menu_sections) {
                $offer->menu_section_ids = implode(",", $request->menu_sections);
            }
            $offer->save();
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
        $find = Offer::find($id);
        if ($find) {
            return $this->_view('offers/show', 'backend');
        } else {
            session()->flash('message', _lang('app.not_found'));
            return redirect('offers.index');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        $find = Offer::find($id);
        if ($find) {

            $offers = Offer::where('available_until', '>', date('Y-m-d'))
                    ->where('active', true)
                    ->where('resturant_id', '!=', $find->resturant_id)
                    ->pluck('resturant_id')
                    ->toArray();

            $this->data['resturantes'] = Resturant::where('active', true)
                    ->select('id', 'title_' . $this->lang_code . ' as title')
                    ->whereNotIn('id', $offers)
                    ->get();

            $this->data['menu_sections'] = MenuSection::where('resturant_id', $find->resturant_id)->where('active', true)->select('id', 'title_' . $this->lang_code . ' as title')->get();
            $this->data['offer_menu_sections'] = explode(',', $find->menu_section_ids);
            $this->data['offer'] = $find;
            return $this->_view('offers/edit', 'backend');
        } else {
            session()->flash('message', _lang('app.not_found'));
            return redirect('offers.index');
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

        $offer = Offer::find($id);
        if (!$offer) {
            return _json('error', _lang('app.error_is_occured'));
        }
        unset($this->rules['image']);
        if ($request->type == 2 || $request->type == 3) {
            $this->rules['menu_sections'] = 'required';
        }
        if ($request->type != 4) {
            $this->rules['discount'] = 'required';
        }
        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        }
        DB::beginTransaction();
        try {
            $offer->resturant_id = $request->resturant;
            $offer->available_until = $request->available_until;
            $offer->type = $request->type;
            $offer->active = $request->active;
            $offer->this_order = $request->this_order;
            if ($request->discount) {
                $offer->discount = $request->discount;
            }
            if ($request->file('image')) {
                $old_image = $offer->image;
                $file = public_path("uploads/offers/$old_image");
                if (!is_dir($file)) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
                $offer->image = $this->_upload($request->file('image'), 'offers', true, '\App\Models\Offer');
            }
            if ($request->menu_sections) {
                $offer->menu_section_ids = implode(",", $request->menu_sections);
            }
            $offer->save();
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
        $offer = Offer::find($id);
        if (!$offer) {
            return _json('error', _lang('app.error_is_occured'), 400);
        }
        try {
            $old_image = $offer->image;
            $offer->delete();

            $file = public_path("uploads/offers/$old_image");
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

    public function data(Request $request) {
        $offers = Offer::join('resturantes', 'offers.resturant_id', '=', 'resturantes.id')
                ->select([
            'offers.id', "resturantes.title_" . $this->lang_code . " as resturant", "offers.image", 'offers.active', "offers.discount", "offers.this_order", "available_until"
        ]);
        return \Datatables::eloquent($offers)
                        ->addColumn('options', function ($item) {

                            $back = "";
                            if (\Permissions::check('offers', 'edit') || \Permissions::check('offers', 'delete')) {
                                $back .= '<div class="btn-group">';
                                $back .= ' <button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> options';
                                $back .= '<i class="fa fa-angle-down"></i>';
                                $back .= '</button>';
                                $back .= '<ul class = "dropdown-menu" role = "menu">';
                                if (\Permissions::check('offers', 'edit')) {
                                    $back .= '<li>';
                                    $back .= '<a href="' . route('offers.edit', $item->id) . '" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.edit');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }

                                if (\Permissions::check('offers', 'delete')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" data-toggle="confirmation" onclick = "Offers.delete(this);return false;" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.delete');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }
                                $back .= '</ul>';
                                $back .= ' </div>';
                            }
                            return $back;
                        })
                        ->editColumn('image', function ($item) {
                            $back = '<img src="' . url('public/uploads/offers/' . $item->image) . '" style="height:64px;width:64px;"/>';
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
