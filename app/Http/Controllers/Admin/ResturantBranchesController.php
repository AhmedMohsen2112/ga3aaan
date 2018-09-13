<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;
use App\Models\ResturantBranch;
use App\Models\Resturant;
use App\Models\City;
use Validator;
use App\Models\ResturantBranchDeliveryPlace;
use DB;

class ResturantBranchesController extends BackendController {

    private $rules = array(
        'title_ar' => 'required',
        'title_en' => 'required',
        'city' => 'required',
        'region' => 'required',
        'resturant' => 'required',
        'delivery_places' => 'required',
    );

    public function __construct() {
        parent::__construct();
        $this->middleware('CheckPermission:resturant_branches,view', ['only' => ['index']]);
        $this->middleware('CheckPermission:resturant_branches,add', ['only' => ['store']]);
        $this->middleware('CheckPermission:resturant_branches,edit', ['only' => ['show', 'update']]);
        $this->middleware('CheckPermission:resturant_branches,delete', ['only' => ['delete']]);
    }

    public function index(Request $request) {
        $resturant_id = $request->input('resturant');
        $resturant = Resturant::where('id', $resturant_id)->select('id', 'title_' . $this->lang_code . ' as title')->first();
        if (!$resturant) {
            session()->flash('message', _lang('app.not_found'));
            return redirect()->back();
        }
        $this->data['resturant'] = $resturant;

        return $this->_view('resturant_branches/index', 'backend');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {

        $resturant_id = $request->input('resturant');
        $resturant = Resturant::where('id', $resturant_id)->select('id', 'title_' . $this->lang_code . ' as title')->first();
        $this->data['cities'] = City::where('active', true)->where('parent_id', 0)->select('id', 'title_' . $this->lang_code . ' as title')->orderBy('this_order')->get();
        $this->data['resturant'] = $resturant;

        return $this->_view('resturant_branches/create', 'backend');
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
        DB::beginTransaction();
        try {
            $check_name = ResturantBranch::where('resturant_id', $request->resturant)
                    ->where('title_ar', $request->title_ar)
                    ->where('title_en', $request->title_en)
                    ->first();
            if ($check_name) {
                return _json('error', _lang('app.the_name_of_the_branch_is_taken_before'));
            }

            $resturant_branch = ResturantBranch::where('resturant_id', $request->resturant)
                    ->where('city_id', $request->city)
                    ->where('region_id', $request->region)
                    ->first();
            if ($resturant_branch) {
                return _json('error', _lang('app.this_branch_is_already_exist'));
            }
            $resturant = Resturant::find($request->input('resturant'));
            $resturant_branch = new ResturantBranch;

            $resturant_branch->title_ar = $request->input('title_ar');
            $resturant_branch->title_en = $request->input('title_en');
            $resturant_branch_slug = str_slug($resturant_branch->title_en);
            $resturant_branch->slug = $resturant->slug . '-' . $resturant_branch_slug;
            $resturant_branch->city_id = $request->input('city');
            $resturant_branch->region_id = $request->input('region');
            $resturant_branch->resturant_id = $request->input('resturant');
            $resturant_branch->lat = $request->input('lat');
            $resturant_branch->lng = $request->input('lng');
            $resturant_branch->active = $request->input('active');
            $resturant_branch->save();

            if ($request->delivery_places) {
                $data = array();
                $filtered = array();

                foreach ($request->delivery_places as $v) {
                    if (isset($filtered[$v['region_id']])) {
                        continue;
                    }
                    $filtered[$v['region_id']] = $v;
                }

                $delivery_places = array_values($filtered);

                foreach ($delivery_places as $delivery_place) {
                    $data[] = array(
                        'resturant_branch_id' => $resturant_branch->id,
                        'region_id' => $delivery_place['region_id'],
                        'delivery_cost' => ($delivery_place['delivery_cost'] == null) ? 0 : $delivery_place['delivery_cost']
                    );
                }
                ResturantBranchDeliveryPlace::insert($data);
            }

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
        $find = ResturantBranch::find($id);
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

        $resturant_branch = ResturantBranch::find($id);

        $resturant = Resturant::where('id', $resturant_branch->resturant_id)->select('id', 'title_' . $this->lang_code . ' as title')->first();

        $this->data['cities'] = City::where('active', true)->where('parent_id', 0)->select('id', 'title_' . $this->lang_code . ' as title')->orderBy('this_order')->get();

        $this->data['regions'] = City::where('active', true)->where('parent_id', $resturant_branch->city_id)->select('id', 'title_' . $this->lang_code . ' as title')->orderBy('this_order')->get();

        $this->data['branch_delivery_places'] = ResturantBranchDeliveryPlace::where('resturant_branch_id', $resturant_branch->id)->get();

        $this->data['resturant_branch'] = $resturant_branch;
        $this->data['resturant'] = $resturant;

        return $this->_view('resturant_branches/edit', 'backend');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {

        $resturant_branch = ResturantBranch::find($id);
        if (!$resturant_branch) {
            return _json('error', _lang('app.error_is_occured'));
        }



        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _json('error', $errors);
        }


        DB::beginTransaction();
        try {
            $check_name = ResturantBranch::where('resturant_id', $request->resturant)
                    ->where('title_ar', $request->title_ar)
                    ->where('title_en', $request->title_en)
                    ->where('id', '!=', $id)
                    ->first();
            if ($check_name) {
                return _json('error', _lang('app.the_name_of_the_branch_is_taken_before'));
            }
            $check = ResturantBranch::where('resturant_id', $request->resturant)
                    ->where('city_id', $request->city)
                    ->where('region_id', $request->region)
                    ->where('id', '!=', $id)
                    ->first();

            if ($check) {
                return _json('error', _lang('app.this_branch_is_already_exist'));
            }

            $resturant = Resturant::find($request->input('resturant'));

            $resturant_branch->title_ar = $request->input('title_ar');
            $resturant_branch->title_en = $request->input('title_en');
            $resturant_branch_slug = str_slug($resturant_branch->title_en);
            $resturant_branch->slug = $resturant->slug . '-' . $resturant_branch_slug;
            $resturant_branch->city_id = $request->input('city');
            $resturant_branch->region_id = $request->input('region');
            $resturant_branch->resturant_id = $request->input('resturant');
            $resturant_branch->lat = $request->input('lat');
            $resturant_branch->lng = $request->input('lng');
            $resturant_branch->active = $request->input('active');
            $resturant_branch->save();

            $resturant_branch->delivery_places()->detach();

            if ($request->delivery_places) {
                $data = array();
                $filtered = array();

                foreach ($request->delivery_places as $v) {
                    if (isset($filtered[$v['region_id']])) {
                        continue;
                    }
                    $filtered[$v['region_id']] = $v;
                }

                $delivery_places = array_values($filtered);

                foreach ($delivery_places as $delivery_place) {
                    $data[] = array(
                        'resturant_branch_id' => $resturant_branch->id,
                        'region_id' => $delivery_place['region_id'],
                        'delivery_cost' => ($delivery_place['delivery_cost'] == null) ? 0 : $delivery_place['delivery_cost']
                    );
                }
                ResturantBranchDeliveryPlace::insert($data);
            }

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
        $resturant_branch = ResturantBranch::find($id);
        if (!$resturant_branch) {
            return _json('error', _lang('app.error_is_occured'), 400);
        }
        try {
            $resturant_branch->delete();
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
        $resturant_branches = ResturantBranch::
                join('cities as city', 'resturant_branches.city_id', '=', 'city.id')
                ->join('cities as region', 'resturant_branches.region_id', '=', 'region.id')
                ->where('resturant_branches.resturant_id', $resturant_id)
                ->select([
            "resturant_branches.id",
            "city.title_" . $this->lang_code . " as city",
            "region.title_" . $this->lang_code . " as region",
            "resturant_branches.active"
        ]);

        return \Datatables::eloquent($resturant_branches)
                        ->addColumn('options', function ($item) {

                            $back = "";
                            if (\Permissions::check('resturant_branches', 'edit') || \Permissions::check('resturant_branches', 'delete')) {
                                $back .= '<div class="btn-group">';
                                $back .= ' <button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> options';
                                $back .= '<i class="fa fa-angle-down"></i>';
                                $back .= '</button>';
                                $back .= '<ul class = "dropdown-menu" role = "menu">';

                                if (\Permissions::check('resturant_branches', 'edit')) {
                                    $back .= '<li>';
                                    $back .= '<a href="' . route('resturant_branches.edit', $item->id) . '" data-id = "' . $item->id . '">';
                                    $back .= '<i class = "icon-docs"></i>' . _lang('app.edit');
                                    $back .= '</a>';
                                    $back .= '</li>';
                                }

                                if (\Permissions::check('resturant_branches', 'delete')) {
                                    $back .= '<li>';
                                    $back .= '<a href="" data-toggle="confirmation" onclick = "ResturantBranches.delete(this);return false;" data-id = "' . $item->id . '">';
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
