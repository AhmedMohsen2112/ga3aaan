<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;
use App\Models\Ad;
use App\Models\Resturant;
use Validator;
use Image;

class AdsController extends BackendController {

    private $rules = array(
        'url' => 'required|url',
        'this_order' => 'required',
        'ad_image' => 'required|image|mimes:gif,png,jpeg|max:1000',
    );

    public function __construct() {

        parent::__construct();
        $this->middleware('CheckPermission:ads,open', ['only' => ['index']]);
        $this->middleware('CheckPermission:ads,add', ['only' => ['store']]);
        $this->middleware('CheckPermission:ads,edit', ['only' => ['show', 'update']]);
        $this->middleware('CheckPermission:ads,delete', ['only' => ['delete']]);
    }

    public function index() {
        $this->data['resturantes'] = Resturant::select('id', "title_$this->lang_code as title")->get();
        return $this->_view('ads/index', 'backend');
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

        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        } else {
            $Ad = new Ad;

            $Ad->url = $request->input('url');
            $Ad->ad_image = $this->_upload($request->file('ad_image'), 'ads', true, '\App\Models\Ad');
            $Ad->active = $request->input('active');
            $Ad->this_order = $request->input('this_order');

            try {
                $Ad->save();
                return _json('success', _lang('app.added_successfully'));
            } catch (Exception $ex) {
                return _json('error', _lang('app.error_is_occured'));
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $find = Ad::find($id);
        if ($find) {
            return response()->json([
                        'type' => 'success',
                        'message' => $find
            ]);
        } else {
            return response()->json([
                        'type' => 'success',
                        'message' => 'error'
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        unset($this->rules['ad_image']);
        $Ad = Ad::find($id);
        if (!$Ad) {
            return _json('error', _lang('app.error_is_occured'));
        }
        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        } else {

            $Ad->url = $request->input('url');
            if ($request->file('ad_image')) {
                $old_image = $Ad->ad_image;
                $this->deleteUploaded('ads', $old_image, '\App\Models\Ad');

                $Ad->ad_image = $this->_upload($request->file('ad_image'), 'ads', true, '\App\Models\Ad');
            }
            $Ad->active = $request->input('active');
            $Ad->this_order = $request->input('this_order');

            try {
                $Ad->save();
                return _json('success', _lang('app.updated_successfully'));
            } catch (Exception $ex) {
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
        $Ad = Ad::find($id);
        if (!$Ad) {
            return _json('error', _lang('app.error_is_occured'));
        }
        try {
            $this->deleteUploaded('ads', $Ad->ad_image, '\App\Models\Ad');
            $Ad->delete();
            return _json('success', _lang('app.deleted_successfully'));
        } catch (Exception $ex) {
            return _json('error', _lang('app.error_is_occured'));
        }
    }

    public function data() {
        $Ads = Ad::select(['id',"url", "this_order", 'active', 'ad_image']);

        return \Datatables::eloquent($Ads)
                        ->addColumn('options', function ($item) {

                            $back = "";
                            if (\Permissions::check('ads', 'edit') || \Permissions::check('ads', 'delete')) {
                                $back .= '<div class="btn-group">';
                                $back .= ' <button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> options';
                                $back .= '<i class="fa fa-angle-down"></i>';
                                $back .= '</button>';
                                $back .= '<ul class = "dropdown-menu" role = "menu">';
                                if (\Permissions::check('ads', 'edit')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" onclick = "Ads.edit(this);return false;" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.edit');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }
                                if (\Permissions::check('ads', 'delete')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" data-toggle="confirmation" onclick = "Ads.delete(this);return false;" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.delete');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }
                                $back .= '</ul>';
                                $back .= ' </div>';
                            }
                            return $back;
                        })
                        ->addColumn('active', function ($item) {
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
                        ->addColumn('ad_image', function ($item) {
                            $back = '<a href="'.$item->url.'"><img src="' . url('public/uploads/ads/' . $item->ad_image) . '" style="height:64px;width:64px;"/></a>';
                            return $back;
                        })
                        ->rawColumns(['options', 'active', 'ad_image'])
                        ->make(true);
    }

}
