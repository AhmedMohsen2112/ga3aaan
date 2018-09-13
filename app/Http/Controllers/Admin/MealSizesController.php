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

class MealSizesController extends BackendController {

    private $rules = array(
        'size' => 'required',
        'price' => 'required',
        'this_order' => 'required|numeric',
    );

    public function __construct() {
        parent::__construct();
        $this->middleware('CheckPermission:meal_sizes,open', ['only' => ['index']]);
        $this->middleware('CheckPermission:meal_sizes,add', ['only' => ['store']]);
        $this->middleware('CheckPermission:meal_sizes,edit', ['only' => ['show', 'update']]);
        $this->middleware('CheckPermission:meal_sizes,delete', ['only' => ['delete']]);
    }

    public function index(Request $request) {
        $meal = $this->getMeal($request->input('meal'));
        if (!$meal) {
            return $this->err404();
        }
        $this->data['meal'] = $meal;
        return $this->_view('meal_sizes/index', 'backend');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {

        $meal = $this->getMeal($request->input('meal'));
        if (!$meal) {
            return $this->err404();
        }
        $this->data['meal'] = $meal;
        $this->data['sizes'] = Size::select('id', "title_$this->lang_code as title")->get();
        $this->data['choices'] = $this->getChoicesWithSub($meal->resturant_id);
        //dd($this->data['choices']);
        return $this->_view('meal_sizes/create', 'backend');
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
        $check = MealSize::where('meal_id', $request->meal)
                ->where('size_id', $request->size)
                ->first();
        if ($check) {
            return _json('error', _lang('app.this_meal_size_is_already_exist'));
        }
        DB::beginTransaction();
        try {
            // dd($request->all());
            $MealSize = new MealSize;

            $MealSize->meal_id = $request->input('meal');
            $MealSize->size_id = $request->input('size');
            $MealSize->price = $request->input('price');
            $MealSize->this_order = $request->input('this_order');
            $MealSize->active = $request->input('active');

            $MealSize->save();
            $choices_selcted = $request->input('selected');
            $choices = $request->input('choices');
            $sub_choices = $request->input('sub_choices');
            if ($choices_selcted && count($choices_selcted) > 0) {
                foreach ($choices_selcted as $choice) {
                    $MealChoice = new MealChoice;
                    $MealChoice->meal_size_id = $MealSize->id;
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
        $find = MealSize::find($id);
        if (!$find) {
            return $this->err404();
        }
        $meal = $this->getMeal($find->meal_id);
        $meal_size_choices = $find->choices->keyBy('choice_id');
        if ($meal_size_choices->count() > 0) {
            foreach ($meal_size_choices as $one) {
                $one->sub = MealSubChoice::where('meal_choice_id', $one->meal_choice_id)->pluck('sub_choice_id');
            }
        }
        $this->data['meal'] = $meal;
        $this->data['meal_size'] = $find;
        $this->data['meal_size_choices'] = $meal_size_choices;
        $this->data['sizes'] = Size::select('id', "title_$this->lang_code as title")->get();
        $this->data['choices'] = $this->getChoicesWithSub($meal->resturant_id);
        //dd($this->data['choices']);
        return $this->_view('meal_sizes/edit', 'backend');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $MealSize = MealSize::find($id);
        if (!$MealSize) {
            return _json('error', _lang('app.error_is_occured'));
        }

        $this->get_choices_rules($request);
        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        }
        $check = MealSize::where('meal_id', $MealSize->meal_id)
                ->where('size_id', $MealSize->size_id)
                ->where('id', '!=', $id)
                ->first();
        if ($check) {
            return _json('error', _lang('app.this_meal_size_is_already_exist'));
        }
        DB::beginTransaction();
        try {

            $MealSize->size_id = $request->input('size');
            $MealSize->price = $request->input('price');
            $MealSize->this_order = $request->input('this_order');
            $MealSize->active = $request->input('active');

            $MealSize->save();

            $choices_selcted = $request->input('selected');
            $choices = $request->input('choices');
            $sub_choices = $request->input('sub_choices');

            if ($MealSize->choices->count() > 0) {
                foreach ($MealSize->choices as $one) {
                    MealSubChoice::where('meal_choice_id', $one->meal_choice_id)->delete();
                }

                MealChoice::where('meal_size_id', $MealSize->id)->delete();
            }
            if ($choices_selcted && count($choices_selcted) > 0) {
                foreach ($choices_selcted as $choice) {
                    $MealChoice = new MealChoice;
                    $MealChoice->meal_size_id = $MealSize->id;
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
        $MealSize = MealSize::find($id);
        if (!$MealSize) {
            return _json('error', _lang('app.error_is_occured'));
        }
        try {
            if ($MealSize->choices->count() > 0) {
                foreach ($MealSize->choices as $one) {
                    MealSubChoice::where('meal_choice_id', $one->meal_choice_id)->delete();
                }

                MealChoice::where('meal_size_id', $MealSize->id)->delete();
            }
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
        $meal_sizes = MealSize::join('meals', 'meals.id', '=', 'meal_sizes.meal_id')
                ->join('sizes', 'sizes.id', '=', 'meal_sizes.size_id')
                ->where('meals.id', $request->input('meal'))
                ->select([
            'meal_sizes.id', "sizes.title_" . $this->lang_code . " as title", "meal_sizes.this_order", 'meal_sizes.active'
        ]);

        return \Datatables::eloquent($meal_sizes)
                        ->addColumn('options', function ($item) {

                            $back = "";
                            if (\Permissions::check('meal_sizes', 'edit') || \Permissions::check('meal_sizes', 'delete')) {
                                $back .= '<div class="btn-group">';
                                $back .= ' <button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> options';
                                $back .= '<i class="fa fa-angle-down"></i>';
                                $back .= '</button>';
                                $back .= '<ul class = "dropdown-menu" role = "menu">';

                                if (\Permissions::check('meal_sizes', 'edit')) {
                                    $back .= '<li>';
                                    $back .= '<a href="' . route('meal_sizes.edit', $item->id) . '" >';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.edit');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }
                                if (\Permissions::check('meal_sizes', 'delete')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" data-toggle="confirmation" onclick = "MealSizes.delete(this);return false;" data-id = "' . $item->id . '">';
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

    private function getMeal($id) {
        $meal = Meal::join('menu_sections', 'menu_sections.id', '=', 'meals.menu_section_id')
                ->join('resturantes', 'resturantes.id', '=', 'menu_sections.resturant_id')
                ->where('meals.id', $id)
                ->select('meals.id', "resturantes.id as resturant_id", "menu_sections.id as menu_section_id")
                ->first();
        return $meal;
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
