<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;
use App\Models\Meal;
use App\Models\MealSize;
use App\Models\MenuSection;
use App\Models\Resturant;
use App\Models\Choice;
use App\Models\SubChoice;
use App\Models\MealChoice;
use App\Models\MealSubChoice;
use App\Models\Size;
use App\Models\MealTopping;
use DB;
use Validator;
use Session;

class MealsController extends BackendController {

    private $rules = array(
        'title_ar' => 'required',
        'title_en' => 'required',
        'description_ar' => 'required',
        'description_en' => 'required',
        'image' => 'required|image|mimes:gif,png,jpeg|max:1000',
        'price' => 'required',
        'menu_section' => 'required|numeric',
        'this_order' => 'required|numeric',
    );

    public function __construct() {
        parent::__construct();
        $this->middleware('CheckPermission:meals,view', ['only' => ['index']]);
        $this->middleware('CheckPermission:meals,add', ['only' => ['store']]);
        $this->middleware('CheckPermission:meals,edit', ['only' => ['show', 'update']]);
        $this->middleware('CheckPermission:meals,delete', ['only' => ['delete']]);
    }

    public function index(Request $request) {
        $menu_section = $this->getMenuSection($request->input('menu_section'));
        if (!$menu_section) {
            return $this->err404();
        }
        $this->data['menu_section'] = $menu_section;
        return $this->_view('meals/index', 'backend');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {

        $menu_section = $this->getMenuSection($request->input('menu_section'));
        if (!$menu_section) {
            return $this->err404();
        }
        $this->data['menu_section'] = $menu_section;
        $this->data['choices'] = $this->getChoicesWithSub($menu_section->resturant_id);
        //dd($this->data['choices']);
        return $this->_view('meals/create', 'backend');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {

        $this->get_choices_rules($request);
        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        }


        $check = Meal::where(function ($query)use ($request) {
                    $query->where('title_ar', $request->title_ar);
                    $query->orWhere('title_en', $request->title_en);
                })
                ->where('menu_section_id', $request->menu_section)
                ->first();
        if ($check) {
            return _json('error', _lang('app.this_meal_is_already_exist'));
        }
        DB::beginTransaction();
        try {
            // dd($request->all());
            $meal = new Meal;

            $meal->title_ar = $request->input('title_ar');
            $meal->title_en = $request->input('title_en');
            $meal->slug = str_slug($request->input('title_en'));
            $meal->description_ar = $request->input('description_ar');
            $meal->description_en = $request->input('description_en');
            $meal->menu_section_id = $request->input('menu_section');
            $meal->this_order = $request->input('this_order');
            $meal->price = $request->input('price');
            $meal->active = $request->input('active');
            $meal->has_sizes = $request->input('has_sizes');
            $meal->image = $this->_upload($request->file('image'), 'meals', true, '\App\Models\Meal');
            $meal->save();
            $choices_selcted = $request->input('selected');
            $choices = $request->input('choices');
            $sub_choices = $request->input('sub_choices');
            if ($choices_selcted && count($choices_selcted) > 0) {
                foreach ($choices_selcted as $choice) {
                    $MealChoice = new MealChoice;
                    $MealChoice->meal_id = $meal->id;
                    $MealChoice->choice_id = $choice;
                    $MealChoice->min = $choices[$choice]['min'];
                    $MealChoice->max = $choices[$choice]['max'];
                    $MealChoice->save();
                    if (isset($sub_choices[$choice])) {
                        $MealSubChoiceData = [];
                        foreach ($sub_choices[$choice] as $sub_choice) {
                            $MealSubChoiceData[] = array(
                                'meal_choice_id' => $MealChoice->id,
                                'sub_choice_id' => $sub_choice
                            );
                        }
                    }
                    MealSubChoice::insert($MealSubChoiceData);
                }
            }
            DB::commit();
            return _json('success', _lang('app.added_successfully'));
        } catch (\Exception $e) {
            DB::rollback();
            return _json('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $find = Meal::find($id);
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
        $find = Meal::find($id);
        if (!$find) {
            return $this->err404();
        }
        $menu_section = $this->getMenuSection($find->menu_section_id);
        //dd($find->choices->keyBy('choice_id'));
        $this->data['meal'] = $find;
        $meal_choices = $find->choices->keyBy('choice_id');
        if ($meal_choices->count() > 0) {
            foreach ($meal_choices as $one) {
                $one->sub = MealSubChoice::where('meal_choice_id', $one->meal_choice_id)->pluck('sub_choice_id');
            }
        }
        //dd($meal_choices);
        $this->data['meal_choices'] = $meal_choices;
        $this->data['menu_section'] = $menu_section;
        $this->data['choices'] = $this->getChoicesWithSub($menu_section->resturant_id);
        return $this->_view('meals/edit', 'backend');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $meal = Meal::find($id);
        if (!$meal) {
            return _json('error', _lang('app.error_is_occured'));
        }
        $this->get_choices_rules($request);
        unset($this->rules['image']);
        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        }
        $check = Meal::where(function ($query)use ($request) {
                    $query->where('title_ar', $request->title_ar);
                    $query->orWhere('title_en', $request->title_en);
                })
                ->where('menu_section_id', $request->menu_section)
                ->where('id', '!=', $id)
                ->first();
        if ($check) {
            return _json('error', _lang('app.this_meal_is_already_exist'));
        }
        DB::beginTransaction();
        try {

            $meal->title_ar = $request->input('title_ar');
            $meal->title_en = $request->input('title_en');
            $meal->slug = str_slug($request->input('title_en'));
            $meal->description_ar = $request->input('description_ar');
            $meal->description_en = $request->input('description_en');
            $meal->this_order = $request->input('this_order');
            $meal->price = $request->input('price');
            $meal->active = $request->input('active');
            $meal->has_sizes = $request->input('has_sizes');
            if ($request->file('image')) {
                $old_image = $meal->image;
                $this->deleteUploaded('meals', $old_image, '\App\Models\Meal');
                $meal->image = $this->_upload($request->file('image'), 'meals', true, '\App\Models\Meal');
            }
            $meal->save();

            $choices_selcted = $request->input('selected');
            $choices = $request->input('choices');
            $sub_choices = $request->input('sub_choices');

            if ($meal->choices->count() > 0) {
                foreach ($meal->choices as $one) {
                    MealSubChoice::where('meal_choice_id', $one->meal_choice_id)->delete();
                }

                MealChoice::where('meal_id', $meal->id)->delete();
            }
            if ($choices_selcted && count($choices_selcted) > 0) {
                foreach ($choices_selcted as $choice) {
                    $MealChoice = new MealChoice;
                    $MealChoice->meal_id = $meal->id;
                    $MealChoice->choice_id = $choice;
                    $MealChoice->min = $choices[$choice]['min'];
                    $MealChoice->max = $choices[$choice]['max'];
                    $MealChoice->save();
                    if (isset($sub_choices[$choice])) {
                        $MealSubChoiceData = [];
                        foreach ($sub_choices[$choice] as $sub_choice) {
                            $MealSubChoiceData[] = array(
                                'meal_choice_id' => $MealChoice->id,
                                'sub_choice_id' => $sub_choice
                            );
                        }
                    }
                    MealSubChoice::insert($MealSubChoiceData);
                }
            }
            DB::commit();
            return _json('success', _lang('app.updated_successfully'));
        } catch (\Exception $e) {
            DB::rollback();
            return _json('error', $e->getMessage() . $e->getLine() . $e->getFile());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $meal = Meal::find($id);
        if (!$meal) {
            return _json('error', _lang('app.error_is_occured'));
        }
        try {
            $old_image = $meal->image;
            $meal->delete();
            $this->deleteUploaded('meals', $old_image, '\App\Models\Meal');
            return _json('success', _lang('app.deleted_successfully'));
        } catch (\Exception $ex) {
            if ($ex->getCode() == 23000) {
                return _json('error', _lang('app.this_record_can_not_be_deleted_for_linking_to_other_records'), 400);
            } else {

                return _json('error', _lang('app.error_is_occured'), 400);
            }
        }
    }

    public function destroy_size($id) {
        $MealSize = MealSize::find($id);
        if (!$MealSize) {
            return _json('error', _lang('app.error_is_occured'));
        }
        try {
            $MealSize->delete();
            return _json('success', _lang('app.deleted_successfully'));
        } catch (\Exception $ex) {
            if ($ex->getCode() == 23000) {
                return _json('error', _lang('app.this_record_can_not_be_deleted_for_linking_to_other_records'), 400);
            } else {
                return _json('error', _lang('app.error_is_occured'), 400);
            }
        }
    }

    public function destroy_topping($id) {
        $MealTopping = MealTopping::find($id);
        if (!$MealTopping) {
            return _json('error', _lang('app.error_is_occured'));
        }
        try {
            $MealTopping->delete();
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
        //dd($request->input('menu_section'));
        $meals = Meal::where('menu_section_id', $request->input('menu_section'))
                ->select([
            'id', "title_" . $this->lang_code . " as title", "this_order", 'active', 'has_sizes'
        ]);

        return \Datatables::eloquent($meals)
                        ->addColumn('options', function ($item) {

                            $back = "";
                            if (\Permissions::check('meals', 'edit') || \Permissions::check('meals', 'delete')) {
                                $back .= '<div class="btn-group">';
                                $back .= ' <button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> options';
                                $back .= '<i class="fa fa-angle-down"></i>';
                                $back .= '</button>';
                                $back .= '<ul class = "dropdown-menu" role = "menu">';

                                if (\Permissions::check('meals', 'edit')) {
                                    $back .= '<li>';
                                    $back .= '<a href="' . route('meals.edit', $item->id) . '" >';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.edit');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }
                                if (\Permissions::check('meals', 'delete')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" data-toggle="confirmation" onclick = "Meals.delete(this);return false;" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.delete');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }
                                if (\Permissions::check('meal_sizes', 'open') && $item->has_sizes == 1) {
                                    $back .= '<li>';
                                    $back .= '<a href="' . route('meal_sizes.index') . '?meal=' . $item->id . '"  class="data-box">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.sizes');
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

    private function getChoicesWithSub($resturant_id) {
        $result = [];
        $choices = Choice::join('resturantes', 'resturantes.id', '=', 'choices.resturant_id')
                ->where('resturantes.id', $resturant_id)
                ->select('choices.id',DB::raw("REPLACE(choices.title_$this->lang_code, '\'', ' ') as title"))
                ->get();
        if ($choices->count() > 0) {
            foreach ($choices as $one) {
                $one->sub = SubChoice::where('choice_id', $one->id)
                        ->select('sub_choices.id',DB::raw("REPLACE(sub_choices.title_$this->lang_code, '\'', ' ') as title"), "sub_choices.price")
                        ->get();
                if ($one->sub->count() > 0) {
                    $result[] = $one;
                }
            }
        }
        return collect($result);
    }
    private function getChoicesWithSub2($resturant_id) {
        $result = [];
        $choices = Choice::join('resturantes', 'resturantes.id', '=', 'choices.resturant_id')
                ->where('resturantes.id', $resturant_id)
                ->select('choices.id', "choices.title_$this->lang_code as title")
                ->get();
        if ($choices->count() > 0) {
            foreach ($choices as $one) {
                $one->sub = SubChoice::where('choice_id', $one->id)
                        ->select('sub_choices.id', "sub_choices.title_$this->lang_code as title", "sub_choices.price")
                        ->get();
                if ($one->sub->count() > 0) {
                    $result[] = $one;
                }
            }
        }
        return collect($result);
    }

    private function getMenuSection($id) {
        $menu_section = MenuSection::join('resturantes', 'resturantes.id', '=', 'menu_sections.resturant_id')
                ->where('menu_sections.id', $id)
                ->select('menu_sections.id', "resturantes.id as resturant_id", "menu_sections.title_$this->lang_code as title", "resturantes.title_$this->lang_code as resturant_title")
                ->first();
        return $menu_section;
    }

    private function getMenuSectionToppings($menu_section_id) {
        $toppings = MenuSection::join('menu_section_toppings', 'menu_sections.id', '=', 'menu_section_toppings.menu_section_id')
                ->join('toppings', 'toppings.id', '=', 'menu_section_toppings.topping_id')
                ->where('menu_sections.id', $menu_section_id)
                ->select('menu_section_toppings.id', 'toppings.title_' . $this->lang_code . ' as title')
                ->get();
        return $toppings;
    }

    private function getSizes() {
        $sizes = Size::where('active', true)->orderBy('this_order')->select('id', 'title_' . $this->lang_code . ' as title')->get();
        return $sizes;
    }

    private function get_choices_rules($request) {
        $selected = $request->input('selected');
        //dd($selected);
        if ($selected && count($selected) > 0) {
            foreach ($selected as $choice) {
                $min_rule_name = 'choices.' . $choice . '.min';
                $max_rule_name = 'choices.' . $choice . '.max';
                $sub_rule_name = 'sub_choices.' . $choice;
                $this->rules[$min_rule_name] = 'required';
                $this->rules[$max_rule_name] = 'required';
                $this->rules[$sub_rule_name] = 'required';
            }
        }
    }

}
