<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;
use App\Models\Size;
use Validator;

class SizesController extends BackendController {

    private $rules = array(
        'title_ar' => 'required',
        'title_en' => 'required',
        'this_order' => 'required',
    );

    public function __construct() {
        parent::__construct();
        $this->middleware('CheckPermission:sizes,open', ['only' => ['index']]);
        $this->middleware('CheckPermission:sizes,add', ['only' => ['store']]);
        $this->middleware('CheckPermission:sizes,edit', ['only' => ['show', 'update']]);
        $this->middleware('CheckPermission:sizes,delete', ['only' => ['delete']]);
    }

    public function index() {
        return $this->_view('sizes/index', 'backend');
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
            $errors = $this->inputs_check('\App\Models\Size', array(
                'title_ar' => $request->input('title_ar'),
                'title_en' => $request->input('title_en'),
            ));
            if (!empty($errors)) {
                return _json('error', $errors);
            }

            $Size = new Size;

            $Size->title_ar = $request->input('title_ar');
            $Size->title_en = $request->input('title_en');
            $Size->this_order = $request->input('this_order');
            $Size->active = $request->input('active');


            if ($Size->save()) {
                return _json('success', _lang('app.added_successfully'));
            } else {
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
        $find = Size::find($id);

        if ($find) {
            return _json('success', $find);
        } else {
            return _json('success', 'error');
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

        $Size = Size::find($id);
        if (!$Size) {
            return _json('error', _lang('app.error_is_occured'), 404);
        }
        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        } else {
            $errors = $this->inputs_check('\App\Models\Size', array(
                'title_ar' => $request->input('title_ar'),
                'title_en' => $request->input('title_en'),
                    ), $id);
            if (!empty($errors)) {
                return _json('error', $errors);
            }

            $Size->title_ar = $request->input('title_ar');
            $Size->title_en = $request->input('title_en');
            $Size->this_order = $request->input('this_order');
            $Size->active = $request->input('active');

            if ($Size->save()) {
                return _json('success', _lang('app.updated_successfully'));
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
        $Size = Size::find($id);
        if (!$Size) {
            return _json('error', _lang('app.error_is_occured'), 400);
        }
        try {
            $Size->delete();
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
        $Sizes = Size::select([
                    'id', "title_".$this->lang_code." as title", "this_order", 'active'
        ]);

        return \Datatables::eloquent($Sizes)
                        ->addColumn('options', function ($item) {

                            $back = "";
                            if (\Permissions::check('sizes', 'edit') || \Permissions::check('sizes', 'delete')) {
                                $back .= '<div class="btn-group">';
                                $back .= ' <button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> options';
                                $back .= '<i class="fa fa-angle-down"></i>';
                                $back .= '</button>';
                                $back .= '<ul class = "dropdown-menu" role = "menu">';

                                if (\Permissions::check('sizes', 'edit')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" onclick = "Sizes.edit(this);return false;" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.edit');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }

                                if (\Permissions::check('sizes', 'delete')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" data-toggle="confirmation" onclick = "Sizes.delete(this);return false;" data-id = "' . $item->id . '">';
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
                        ->escapeColumns([])
                        ->make(true);
    }

}
