<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;
use App\Models\City;
use Validator;

class CitiesController extends BackendController {

    private $rules = array(
        'title_ar' => 'required',
        'title_en' => 'required',
        'this_order' => 'required',
    );

    public function __construct() {

        parent::__construct();
        $this->middleware('CheckPermission:cities,open', ['only' => ['index']]);
        $this->middleware('CheckPermission:cities,add', ['only' => ['store']]);
        $this->middleware('CheckPermission:cities,edit', ['only' => ['show', 'update']]);
        $this->middleware('CheckPermission:cities,delete', ['only' => ['delete']]);
    }

    public function index() {

        return $this->_view('cities/index', 'backend');
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
            return response()->json([
                        'type' => 'error',
                        'errors' => $errors
            ]);
        } else {
            $errors = $this->inputs_check('\App\Models\City', array(
                'title_ar' => $request->input('title_ar'),
                'title_en' => $request->input('title_en'),
            ));
            if (!empty($errors)) {
                return response()->json([
                            'type' => 'error',
                            'errors' => $errors
                ]);
            }

            $City = new City;

            $City->title_ar = $request->input('title_ar');
            $City->title_en = $request->input('title_en');
            $City->this_order = $request->input('this_order');
            $City->active = $request->input('active');
            $City->lat = $request->input('lat');
            $City->lng = $request->input('lng');
            $City->parent_id = $request->input('parent_id');
            if ($City->parent_id != 0) {
                $parent = City::find($City->parent_id);
                $City->level = $parent->level + 1;
            } else {
               
                $City->level = 0;
            }

            if ($City->save()) {
                return response()->json([
                            'type' => 'success',
                            'message' => _lang('app.added_successfully')
                ]);
            } else {
                return response()->json([
                            'type' => 'error',
                            'message' => _lang('app.error_is_occured')
                ]);
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
        $find = City::find($id);

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

        $City = City::find($id);
        if (!$City) {
            return response()->json([
                        'type' => 'error',
                        'message' => _lang('app.error_is_occured')
                            ], 404);
        }
        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return response()->json([
                        'type' => 'error',
                        'errors' => $errors
            ]);
        } else {
            $errors = $this->inputs_check('\App\Models\City', array(
                'title_ar' => $request->input('title_ar'),
                'title_en' => $request->input('title_en'),
                    ), $id);
            if (!empty($errors)) {
                return response()->json([
                            'type' => 'error',
                            'errors' => $errors
                ]);
            }
            $City->title_ar = $request->input('title_ar');
            $City->title_en = $request->input('title_en');
            $City->this_order = $request->input('this_order');
            $City->active = $request->input('active');
            $City->lat = $request->input('lat');
            $City->lng = $request->input('lng');
            $City->parent_id = $request->input('parent_id');

            if ($City->save()) {
                return response()->json([
                            'type' => 'success',
                            'message' => _lang('app.updated_successfully')
                ]);
            } else {
                return response()->json([
                            'type' => 'error',
                            'message' => _lang('app.error_is_occured')
                ]);
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
        $City = City::find($id);
        if (!$City) {
            return _json('error', _lang('app.bad_request'), 400);
        }
        try {
            $City->delete();
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
        $parent_id = $request->input('parent_id');
        $cities = City::where('parent_id', $parent_id)->select([
            'id', "title_".$this->lang_code." as title", "this_order", 'active','parent_id','level'
        ]);

        return \Datatables::eloquent($cities)
                        ->addColumn('options', function ($item) {

                            $back = "";
                            if (\Permissions::check('cities', 'edit') || \Permissions::check('cities', 'delete')) {
                                $back .= '<div class="btn-group">';
                                $back .= ' <button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> options';
                                $back .= '<i class="fa fa-angle-down"></i>';
                                $back .= '</button>';
                                $back .= '<ul class = "dropdown-menu" role = "menu">';
                                if (\Permissions::check('cities', 'edit')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" onclick = "Cities.edit(this);return false;" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.edit');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }

                                if (\Permissions::check('cities', 'delete')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" data-toggle="confirmation" onclick = "Cities.delete(this);return false;" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.delete');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }
                                $back .= '</ul>';
                                $back .= ' </div>';
                            }
                            return $back;
                        })
                        ->editColumn('title', function ($item) {
                
                            if ($item->level == 1) {
                                $back = $item->title;
                            } else {
                                $back = '<a class="panel-title data-box" data-where="inTable" data-title="' . $item->title . '" data-level="' . $item->level . '" data-id="' . $item->id . '">' . $item->title . '</a>';
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
