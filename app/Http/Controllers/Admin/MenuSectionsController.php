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

class MenuSectionsController extends BackendController {

    private $rules = array(
        'title_ar' => 'required',
        'title_en' => 'required',
        'this_order' => 'required|numeric',
    );

    public function __construct() {
        parent::__construct();
        $this->middleware('CheckPermission:menu_sections,view', ['only' => ['index']]);
        $this->middleware('CheckPermission:menu_sections,add', ['only' => ['store']]);
        $this->middleware('CheckPermission:menu_sections,edit', ['only' => ['show', 'update']]);
        $this->middleware('CheckPermission:menu_sections,delete', ['only' => ['delete']]);
    }

    public function index(Request $request) {
        $resturant_id = $request->input('resturant');
        $this->data['resturant'] = $this->getResturant($resturant_id);
        return $this->_view('menu_sections/index', 'backend');
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
        }
        $check = MenuSection::where('title_ar', $request->title_ar)
                ->where('title_en', $request->title_en)
                ->where('resturant_id', $request->resturant)
                ->first();
        if ($check) {
            return _json('error', _lang('app.this_menu_section_is_already_exist'));
        }
        DB::beginTransaction();
        try {

            $menuSection = new MenuSection;

            $menuSection->title_ar = $request->input('title_ar');
            $menuSection->title_en = $request->input('title_en');
            $menuSection->slug = str_slug($request->input('title_en'));
            $menuSection->resturant_id = $request->input('resturant');
            $menuSection->this_order = $request->input('this_order');
            $menuSection->active = $request->input('active');
            $menuSection->save();


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
        $find = MenuSection::find($id);
        if ($find) {
            return _json('success', $find);
        } else {
            return _json('success', 'error');
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
        $menuSection = MenuSection::find($id);
        if (!$menuSection) {
            return _json('error', _lang('app.error_is_occured'));
        }
        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        }
        $check = MenuSection::where('title_ar', $request->title_ar)
                ->where('title_en', $request->title_en)
                ->where('resturant_id', $request->resturant)
                ->where('id', '!=', $id)
                ->first();
        if ($check) {
            return _json('error', _lang('app.this_menu_section_is_already_exist'));
        }
        DB::beginTransaction();
        try {
            $menuSection->title_ar = $request->input('title_ar');
            $menuSection->title_en = $request->input('title_en');
            $menuSection->slug = str_slug($request->input('title_en'));
            $menuSection->this_order = $request->input('this_order');
            $menuSection->active = $request->input('active');
            $menuSection->save();

            DB::commit();
            return _json('success', _lang('app.updated_successfully'));
        } catch (\Exception $e) {
            DB::rollback();
            return _json('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $menuSection = MenuSection::find($id);
        if (!$menuSection) {
            return _json('error', _lang('app.error_is_occured'));
        }
        try {
            $menuSection->delete();
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
        $resturant_id = $request->input('resturant');
        $menu_sections = MenuSection::where('resturant_id', $resturant_id)
                ->select([
            'id', "title_" . $this->lang_code . " as title", "this_order", 'active'
        ]);

        return \Datatables::eloquent($menu_sections)
                        ->addColumn('options', function ($item) {

                            $back = "";
                            if (\Permissions::check('menu_sections', 'edit') || \Permissions::check('menu_sections', 'delete')) {
                                $back .= '<div class="btn-group">';
                                $back .= ' <button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> options';
                                $back .= '<i class="fa fa-angle-down"></i>';
                                $back .= '</button>';
                                $back .= '<ul class = "dropdown-menu" role = "menu">';

                                if (\Permissions::check('menu_sections', 'edit')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" data-toggle="confirmation" onclick = "MenuSections.edit(this);return false;" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.edit');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }
                                if (\Permissions::check('menu_sections', 'delete')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" data-toggle="confirmation" onclick = "MenuSections.delete(this);return false;" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.delete');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }
                                if (\Permissions::check('meals', 'view')) {
                                    $back .= '<li>';
                                    $back .= '<a href="' . route('meals.index') . '?menu_section=' . $item->id . '" >';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.meals');
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

    private function getResturant($id) {
        $resturant = Resturant::where('id', $id)->select('id', 'title_' . $this->lang_code . ' as title')->first();
        return $resturant;
    }

}
