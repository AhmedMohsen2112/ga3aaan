<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;
use App\Models\Slider;
use Validator;

class SliderController extends BackendController {

    private $rules = array(
        'url' => 'required',
        'image' => 'required|image|mimes:gif,png,jpeg|max:1000',
    );

    public function __construct() {

        parent::__construct();
        $this->middleware('CheckPermission:slider,open', ['only' => ['index']]);
        $this->middleware('CheckPermission:slider,add', ['only' => ['store']]);
        $this->middleware('CheckPermission:slider,edit', ['only' => ['show', 'update']]);
        $this->middleware('CheckPermission:slider,delete', ['only' => ['delete']]);
    }

    public function index() {
        return $this->_view('slider/index', 'backend');
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
            $Slider = new Slider;

            $Slider->url = $request->input('url');
            $Slider->image = $this->_upload($request->file('image'), 'slider', true, '\App\Models\Slider');
            $Slider->active = $request->input('active');
            $Slider->this_order = $request->input('this_order');

            try {
                $Slider->save();
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
        $find = Slider::find($id);
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
        unset($this->rules['image']);
        $Slider = Slider::find($id);
        if (!$Slider) {
            return _json('error', _lang('app.error_is_occured'));
        }
        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        } else {


            $Slider->url = $request->input('url');
            if ($request->file('image')) {
                $old_image = $Slider->image;
                $this->deleteUploaded('slider', $old_image, '\App\Models\Slider');

                $Slider->image = $this->_upload($request->file('image'), 'slider', true, '\App\Models\Slider');
            }
            $Slider->active = $request->input('active');
            $Slider->this_order = $request->input('this_order');

            try {
                $Slider->save();
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
        $Slider = Slider::find($id);
        if (!$Slider) {
            return _json('error', _lang('app.error_is_occured'));
        }
        try {
            $this->deleteUploaded('slider', $Slider->image, '\App\Models\Slider');
            $Slider->delete();
            return _json('success', _lang('app.deleted_successfully'));
        } catch (Exception $ex) {
            return _json('error', _lang('app.error_is_occured'));
        }
    }

    public function data() {
        $Sliders = Slider::select(['id', "url", "this_order", 'active', 'image']);

        return \Datatables::eloquent($Sliders)
                        ->addColumn('options', function ($item) {

                            $back = "";
                            if (\Permissions::check('slider', 'edit') || \Permissions::check('slider', 'delete')) {
                                $back .= '<div class="btn-group">';
                                $back .= ' <button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> options';
                                $back .= '<i class="fa fa-angle-down"></i>';
                                $back .= '</button>';
                                $back .= '<ul class = "dropdown-menu" role = "menu">';
                                if (\Permissions::check('slider', 'edit')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" onclick = "Slider.edit(this);return false;" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.edit');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }
                                if (\Permissions::check('slider', 'delete')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" data-toggle="confirmation" onclick = "Slider.delete(this);return false;" data-id = "' . $item->id . '">';
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
                        ->addColumn('image', function ($item) {
                            $back = '<img src="' . url('public/uploads/slider/' . $item->image) . '" style="height:64px;width:64px;"/>';
                            return $back;
                        })
                        ->rawColumns(['options', 'active', 'image'])
                        ->make(true);
    }

}
