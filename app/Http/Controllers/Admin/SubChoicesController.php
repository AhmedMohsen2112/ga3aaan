<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;
use App\Models\Resturant;
use App\Models\Choice;
use App\Models\SubChoice;
use Validator;

class SubChoicesController extends BackendController {

    private $rules = array(
        'title_ar' => 'required',
        'title_en' => 'required',
    );

    public function __construct() {
        parent::__construct();
        $this->middleware('CheckPermission:sub_choices,open', ['only' => ['index']]);
        $this->middleware('CheckPermission:sub_choices,add', ['only' => ['store']]);
        $this->middleware('CheckPermission:sub_choices,edit', ['only' => ['show', 'update']]);
        $this->middleware('CheckPermission:sub_choices,delete', ['only' => ['delete']]);
    }

    public function index(Request $request) {
        $Choice = $this->getChoice($request->input('choice'));
        if (!$Choice) {
            return $this->err404();
        }
        $this->data['choice'] = $Choice;
        return $this->_view('sub_choices/index', 'backend');
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
        $this->rules['title_ar'] = "required|unique:sub_choices,title_ar,NULL,id,choice_id,{$request->choice}";
        $this->rules['title_en'] = "required|unique:sub_choices,title_en,NULL,id,choice_id,{$request->choice}";
        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        } else {
            try {
                $SubChoice = new SubChoice;
                $SubChoice->title_ar = $request->input('title_ar');
                $SubChoice->title_en = $request->input('title_en');
                $SubChoice->price = $request->input('price');
                $SubChoice->choice_id = $request->input('choice');
                $SubChoice->save();
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
        $find = SubChoice::find($id);

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

        $SubChoice = SubChoice::find($id);
        if (!$SubChoice) {
            return _json('error', _lang('app.error_is_occured'), 404);
        }
          $this->rules['title_ar'] = "required|unique:sub_choices,title_ar,$id,id,choice_id,{$request->choice}";
        $this->rules['title_en'] = "required|unique:sub_choices,title_en,$id,id,choice_id,{$request->choice}";
        $validator = Validator::make($request->all(), $this->rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        } else {

            try {

                $SubChoice->title_ar = $request->input('title_ar');
                $SubChoice->title_en = $request->input('title_en');
                $SubChoice->price = $request->input('price');
                $SubChoice->save();
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
        $SubChoice = SubChoice::find($id);
        if (!$SubChoice) {
            return _json('error', _lang('app.error_is_occured'), 400);
        }
        try {
            $SubChoice->delete();
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
        $SubChoices = SubChoice::where('choice_id', $request->input('choice'))->select([
            'id', "title_$this->lang_code as title", "price"
        ]);

        return \Datatables::eloquent($SubChoices)
                        ->addColumn('options', function ($item) {

                            $back = "";
                            if (\Permissions::check('sub_choices', 'edit') || \Permissions::check('sub_choices', 'delete')) {
                                $back .= '<div class="btn-group">';
                                $back .= ' <button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> options';
                                $back .= '<i class="fa fa-angle-down"></i>';
                                $back .= '</button>';
                                $back .= '<ul class = "dropdown-menu" role = "menu">';

                                if (\Permissions::check('sub_choices', 'edit')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" onclick = "SubChoices.edit(this);return false;" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.edit');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }

                                if (\Permissions::check('sub_choices', 'delete')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" data-toggle="confirmation" onclick = "SubChoices.delete(this);return false;" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.delete');
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

    private function getChoice($choice_id) {
        $Choice = Choice::join('resturantes', 'resturantes.id', '=', 'choices.resturant_id')
                ->where('choices.id', $choice_id)
                ->select('choices.id', "resturantes.id as resturant_id", "choices.title_$this->lang_code as choice_title", "resturantes.title_$this->lang_code as resturant_title")
                ->first();
        return $Choice;
    }

}
