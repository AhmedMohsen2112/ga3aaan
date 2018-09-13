<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Resturant;
use App\Models\Cuisine;
use App\Models\City;
use Validator;
use DateTime;
use DB;

class ResturantesController extends ApiController {

    private $rules = array(
       // 'city' => 'required',
        'region' => 'required',
    );

    private $resturant_rules = array(
       // 'city' => 'required',
        'resturant_branch_id' => 'required',
        'resturant_id' => 'required',
        'region' => 'required',
    );

    public function __construct() {
        parent::__construct();
    }

    protected function serchForResturantes(Request $request) {
        
        $resturantes = array();
        $validator = Validator::make($request->all(), $this->rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _api_json($resturantes, ['errors' => $errors], 422);
        } else {
            try {
                $resturantes = Resturant::join('resturant_branches', 'resturantes.id', '=', 'resturant_branches.resturant_id');
                $resturantes->join('branch_delivery_places', 'resturant_branches.id', '=', 'branch_delivery_places.resturant_branch_id');
                
                //$resturantes->join('cities as region', 'region.id', '=', 'resturant_branches.region_id');
                $resturantes->where('resturant_branches.city_id', $request->city);
                //$resturantes->where('resturant_branches.region_id', $request->region);

                $resturantes->where('branch_delivery_places.region_id', $request->region);
                if ($request->category) {
                    $resturantes->where('resturantes.category_id', $request->category);
                }
                if ($request->cuisine) {
                    $resturantes->join('resturant_cuisines', 'resturant_cuisines.resturant_id', '=', 'resturantes.id');
                    $resturantes->where('resturant_cuisines.cuisine_id', $request->cuisine);
                }
                //$resturantes->groupBy('resturantes.id');
                if ($request->order_by) {
                    $oreder_by = json_decode($request->order_by);
                    if (in_array(1, $oreder_by)) {
                        $resturantes->orderBy('resturant_branches.rate', 'desc');
                    }
                    if (in_array(2, $oreder_by)) {
                        $resturantes->join('offers', 'offers.resturant_id', '=', 'resturantes.id');
                        $resturantes->where('offers.available_until', '>', date('Y-m-d'));
                        $resturantes->where('offers.active', true);
                    }
                    if (in_array(3, $oreder_by)) {
                        $resturantes->orderBy('resturantes.delivery_time');
                    }
                }
                if ($request->search) {
                    $resturantes->whereRaw(handleKeywordWhere(['resturantes.title_ar', 'resturantes.title_en'], $request->search));
                }
                $resturantes->where('resturant_branches.active', true);
                $resturantes->where('resturantes.available', true);
                $resturantes->where('resturantes.active', true);
                $resturantes->orderBy('options', 'desc');
                $resturantes->select('resturantes.*','resturant_branches.title_' . $this->lang_code . ' as region','resturant_branches.rate','resturant_branches.id as branch_id','branch_delivery_places.delivery_cost', DB::raw("(SELECT Count(*) FROM offers WHERE resturant_id = resturantes.id and active = 1 and available_until > " . date('Y-m-d') . ") as has_offer"));

                $resturantes = $resturantes->paginate($this->limit);
                $message = ['number_of_resturantes' => $resturantes->total()];


                $resturantes = Resturant::transformCollection($resturantes);

                return _api_json($resturantes, $message);
            } catch (\Exception $e) {
                $message = _lang('app.error_is_occured');
                return _api_json($resturantes, ['message' => $e->getMessage()], 422);
            }
        }
    }

    public function show(Request $request)
    {
        $resturantes = array();
        $validator = Validator::make($request->all(), $this->resturant_rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _api_json($resturantes, ['errors' => $errors], 422);
        } else {
            try {
                $resturant = Resturant::join('resturant_branches', 'resturantes.id', '=', 'resturant_branches.resturant_id')
                ->join('branch_delivery_places', 'resturant_branches.id', '=', 'branch_delivery_places.resturant_branch_id')
                ->join('cities as region', 'region.id', '=', 'resturant_branches.region_id')
               
                //->where('resturant_branches.city_id', $request->city)
                //->where('resturant_branches.region_id', $request->region)
                ->where('branch_delivery_places.region_id', $request->region)
                ->where('resturantes.id',$request->resturant_id)
                ->where('resturant_branches.id',$request->resturant_branch_id)
                ->where('resturant_branches.active', true)
                ->where('resturantes.available', true)
                ->select('resturantes.*','resturant_branches.title_' . $this->lang_code . ' as region','resturant_branches.rate','resturant_branches.id as branch_id','branch_delivery_places.delivery_cost', DB::raw("(SELECT Count(*) FROM offers WHERE resturant_id = resturantes.id and active = 1 and available_until > " . date('Y-m-d') . ") as has_offer"))

              ->first();
              
                if ($resturant == null) {
                    return _api_json(new \stdClass(),['message' => _lang('app.sorry_this_resturant_branch_doesnot_deliver_to_your_address')], 422);
                }
               
                 $resturant = Resturant::transform($resturant);
                 return _api_json($resturant);
                
            } catch (\Exception $e) {
                $message = _lang('app.error_is_occured');
                return _api_json(new \stdClass(), ['message' => $message], 422);
            }
        }
        
    }




}
