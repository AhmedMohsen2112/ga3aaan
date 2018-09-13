<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BackendController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Pagination\LengthAwarePaginator;
use App;
use Auth;
use DB;
use Redirect;
use Validator;
use App\Models\User;
use App\Models\Order;
use App\Models\Resturant;
use App\Models\ResturantBranch;
use App\Models\Meal;
use App\Traits\OrderTrait;
use PDF;
use Config;

class OrdersReportsController extends BackendController {

    use OrderTrait;

    private $limit = 10;

    public function __construct() {


        parent::__construct();
        $this->middleware('CheckPermission:orders_reports,open', ['only' => ['index']]);
    }

    public function index(Request $request) {

        if ($request->all()) {
            foreach ($request->all() as $key => $value) {

                if ($value) {
                    $this->data[$key] = $value;
                }
            }
        }
        $this->data['orders'] = $this->getOrders($request);
        $this->data['info'] = $this->getInfo($request);
        $this->data['resturantes'] = $this->getResturantes();
        $resturant = $this->User->type == 1 ? $request->resturant : $this->User->resturant->id;
        $branches = $this->getResturantBranches($resturant);
        $this->data['branches'] = $branches;
        $this->data['users'] = $this->getUsers();
        return $this->_view('orders_reports.index', 'backend');
    }


    public function download(Request $request) {
        //dd($request->all());
        $this->data['orders'] = $this->getOrders($request, false, false);
        $this->data['info'] = $this->getInfo($request);

        Config::set('pdf.margin_top', '30');
        Config::set('pdf.title', _lang('app.report'));
        $pdf = PDF::loadView('main_content.reports.pdf', $this->data,[50,100]);
//        $pdf->getMpdf()->writeHtml(view('main_content.reports.pdf_main', $this->data));
        $filename = date('Y_m_d').  time()  . '_report.pdf';
        return  $pdf->download($filename);
        //return  $pdf->Output();
    }
    public function download2(Request $request) {
        //dd($request->all());
        $this->data['orders'] = $this->getOrders($request, false, false);
        $this->data['info'] = $this->getInfo($request);

        Config::set('pdf.margin_top', '30');
        Config::set('pdf.title', _lang('app.report'));
        $pdf = PDF::loadView('main_content.reports.pdf', $this->data);
//        $pdf->getMpdf()->writeHtml(view('main_content.reports.pdf_main', $this->data));
        $filename = date('Y_m_d').  time()  . '_report.pdf';
        return  $pdf->download($filename);
    }


    private function getOrders($request, $id = false, $paginate = true) {


        $orders = Order::join('resturant_branches', 'orders.resturant_branch_id', '=', 'resturant_branches.id');
        $orders->join('resturantes', 'resturantes.id', '=', 'resturant_branches.resturant_id');
        $orders->join('users', 'users.id', '=', 'orders.user_id');
        $orders->join('payment_methods', 'payment_methods.id', '=', 'orders.payment_method_id');
        $orders->select([
            'orders.id', DB::RAW("CONCAT(users.first_name,' ',users.last_name) as client_name"), DB::RAW("CONCAT(resturantes.title_$this->lang_code,' ',resturant_branches.title_$this->lang_code) as resturant_title"),
            "orders.status", "payment_methods.title_$this->lang_code as payment_method", "orders.total_cost", "orders.created_at", "orders.date", "orders.commission",
            DB::raw('((orders.total_cost*orders.commission)/100) as commission_cost')
        ]);

        if (!$id) {
            $orders = $this->handleWhere($orders, $request);
            $orders->orderBy('orders.date', 'DESC');
            if ($paginate) {
                return $orders->paginate($this->limit)->appends($request->all());
            } else {
                return $orders->get();
            }
        } else {
            $orders->where("orders.id", $id);
            return $orders->first();
        }

        //$bills->orderBy('bills.creatsed_at','DESC');
    }

    private function getInfo($request) {
        $orders = Order::join('resturantes', 'resturantes.id', '=', 'orders.resturant_id');
        $orders->join('resturant_branches', 'orders.resturant_branch_id', '=', 'resturant_branches.id');
        $orders->join('cities as city', 'city.id', '=', 'resturant_branches.city_id');
        $orders->join('cities as region', 'region.id', '=', 'resturant_branches.region_id');
        $orders->join('payment_methods', 'payment_methods.id', '=', 'orders.payment_method_id');
        $orders->select([
            'orders.id', "city.title_$this->lang_code as city_title", "region.title_$this->lang_code as region_title",
            "orders.status", "payment_methods.title_$this->lang_code as payment_method", "orders.total_cost", "orders.created_at"
        ]);
        $orders = $this->handleWhere($orders, $request);


        $orders->select(DB::raw('sum((orders.total_cost*orders.commission)/100) as commission_cost,sum(orders.total_cost) as total_cost'));

        return $orders->first();
    }

    private function getResturantes() {
        $Resturantes = Resturant::select('id', "title_$this->lang_code as title")->get();
        return $Resturantes;
    }

    private function getUsers() {
        $Users = User::select('id', "first_name", "last_name")->get();
        return $Users;
    }

    private function getResturantBranches($resturant_id) {
        $branches = ResturantBranch::where('resturant_id', $resturant_id)->select('id', 'title_' . $this->lang_code . ' as title')->get();
        return $branches;
    }

    private function handleWhere($orders, $request) {
        if ($this->User->type == 1) {
            if ($resturant = $request->input('resturant')) {
                $orders->where("resturantes.id", $resturant);
            }
        } else {
            $orders->where("resturantes.id", $this->User->resturant->id);
        }
        if ($request->all()) {
            if ($from = $request->input('from')) {
                $orders->where("orders.date", ">=", "$from");
            }
            if ($to = $request->input('to')) {
                $orders->where("orders.date", "<=", "$to");
            }
            if ($user = $request->input('user')) {
                $orders->where("orders.user_id", $user);
            }
            if ($branch = $request->input('branch')) {
                $orders->where("resturant_branches.id", $branch);
            }
            if ($order = $request->input('order')) {
                $orders->where("orders.id", $order);
            }
        }
        return $orders;
    }

}
