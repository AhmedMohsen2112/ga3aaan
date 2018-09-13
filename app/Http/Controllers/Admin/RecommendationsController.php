<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;
use App\Models\Recommendation;
use Validator;

class RecommendationsController extends BackendController {

    public function __construct() {

        parent::__construct();
        $this->middleware('CheckPermission:recommendations,open', ['only' => ['index']]);
        $this->middleware('CheckPermission:recommendations,delete', ['only' => ['destroy']]);
    }

    public function index() {
        return $this->_view('recommendations/index', 'backend');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    

    public function destroy(Request $request) {
        $ids = $request->input('ids');
        try {
             Recommendation::destroy($ids);
            return _json('success', _lang('app.deleted_successfully'));
        } catch (Exception $ex) {
            return _json('error', _lang('app.error_is_occured'));
        }
    
       
    }

    public function data() {
        $recommendations = Recommendation::join('users','users.id','=','recommendations.user_id')
        ->select(['recommendations.id', 'recommendations.resturant_name', 'recommendations.region', 'recommendations.created_at','users.first_name','users.last_name','users.email']);

        return \Datatables::eloquent($recommendations)
                        
                        ->addColumn('input', function ($item) {

                            $back = '';

                            $back = '<div class="md-checkbox col-md-4" style="margin-left:40%;">';
                            $back .= '<input type="checkbox" id="' . $item->id . '" data-id="' . $item->id . '" class="md-check check-one-message"  value="">';
                            $back .= '<label for="' . $item->id . '">';
                            $back .= '<span></span>';
                            $back .= '<span class="check"></span>';
                            $back .= '<span class="box"></span>';
                            $back .= '</label>';
                            $back .= '</div>';

                            return $back;
                        })
                        ->addColumn('user', function($item) {
                            return $item->first_name . ' ' . $item->last_name;
                        })
                        ->filterColumn('user', function($query, $keyword) {
                            $query->whereRaw("CONCAT(users.first_name,' ',users.last_name) like ?", ["%{$keyword}%"]);
                        })
                        ->escapeColumns([])
                        ->make(true);
    }

}
