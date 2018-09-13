<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;
use App\Models\Resturant;
use App\Models\Choice;
use Validator;

class ChoicesController extends BackendController {

    private $rules = array(
  
    );

    public function __construct() {
        parent::__construct();
        $this->middleware('CheckPermission:choices,open', ['only' => ['index']]);
        $this->middleware('CheckPermission:choices,add', ['only' => ['store']]);
        $this->middleware('CheckPermission:choices,edit', ['only' => ['show', 'update']]);
        $this->middleware('CheckPermission:choices,delete', ['only' => ['delete']]);
    }

    public function index(Request $request) {
        $resturant = Resturant::find($request->input('resturant'));
        if (!$resturant) {
            return $this->err404();
        }
        $this->data['resturant'] = $resturant;
        return $this->_view('choices/index', 'backend');
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
        $this->rules['title_ar'] = "required|unique:choices,title_ar,NULL,id,resturant_id,{$request->resturant}";
        $this->rules['title_en'] = "required|unique:choices,title_en,NULL,id,resturant_id,{$request->resturant}";
        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        } else {
            try {
                $Choice = new Choice;
                $Choice->title_ar = $request->input('title_ar');
                $Choice->title_en = $request->input('title_en');
                $Choice->resturant_id = $request->input('resturant');
                $Choice->save();
                return _json('success', _lang('app.added_successfully'));
            } catch (\Exception $ex) {
                return _json('error', $ex->getMessage(), 400);
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
        $find = Choice::find($id);

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

        $Choice = Choice::find($id);
        if (!$Choice) {
            return _json('error', _lang('app.error_is_occured'), 404);
        }
        $this->rules['title_ar'] = "required|unique:choices,title_ar,$id,id,resturant_id,{$request->resturant}";
        $this->rules['title_en'] = "required|unique:choices,title_en,$id,id,resturant_id,{$request->resturant}";
        $validator = Validator::make($request->all(), $this->rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        } else {

            try {

                $Choice->title_ar = $request->input('title_ar');
                $Choice->title_en = $request->input('title_en');
                $Choice->save();
                return _json('success', _lang('app.updated_successfully'));
            } catch (\Exception $ex) {
                return _json('error', _lang('app.error_is_occured'), 400);
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
        $Choice = Choice::find($id);
        if (!$Choice) {
            return _json('error', _lang('app.error_is_occured'), 400);
        }
        try {
            $Choice->delete();
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
        $Choices = Choice::where('resturant_id', $request->input('resturant'))->select([
            'id', "title_$this->lang_code as title",
        ]);

        return \Datatables::eloquent($Choices)
                        ->addColumn('options', function ($item) {

                            $back = "";
                            if (\Permissions::check('choices', 'edit') || \Permissions::check('choices', 'delete')) {
                                $back .= '<div class="btn-group">';
                                $back .= ' <button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> options';
                                $back .= '<i class="fa fa-angle-down"></i>';
                                $back .= '</button>';
                                $back .= '<ul class = "dropdown-menu" role = "menu">';

                                if (\Permissions::check('choices', 'edit')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" onclick = "Choices.edit(this);return false;" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.edit');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }

                                if (\Permissions::check('choices', 'delete')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" data-toggle="confirmation" onclick = "Choices.delete(this);return false;" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.delete');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }
                                if (\Permissions::check('sub_choices', 'open')) {
                                    $back .= '<li>';
                                    $back .= '<a href="' . route('sub_choices.index') . '?choice=' . $item->id . '"  class="data-box">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.sub_choices');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }
                                $back .= '</ul>';
                                $back .= ' </div>';
                            }
                            return $back;
                        })
                        ->escapeColumns([])
                        ->make(true);
    }

}
