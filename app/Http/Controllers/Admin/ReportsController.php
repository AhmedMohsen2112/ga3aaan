<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;
use App\Models\Resturant;
use App\Models\Order;
use Validator;
use DB;

class ReportsController extends BackendController {

    private $rules = array(
        'from' => 'required',
        'to' => 'required',
        'resturant_id' => 'required'
        
    );

    public function __construct() {
        parent::__construct();
        $this->middleware('CheckPermission:reports,open', ['only' => ['index']]);
        $this->middleware('CheckPermission:reports,add', ['only' => ['store']]);
        $this->middleware('CheckPermission:reports,edit', ['only' => ['show', 'update']]);
        $this->middleware('CheckPermission:reports,delete', ['only' => ['delete']]);
    }

    public function index(Request $request) {     
                     
        return $this->_view('reports/index', 'backend');
    }

    public function ordersReports(Request $request)
    {
        $from = $request->from;
        $to = $request->to;

        $orders = Order::where('resturant_id',$request->resturant_id)
                       ->whereBetween('date',[$from,$to])
                       ->select('orders.*',DB::raw('( total_cost - ((total_cost * commission) /100) ) as commission'))
                        //->paginate($this->limit)

                       ->get();
        dd($orders->toArray());
    }





   

}
