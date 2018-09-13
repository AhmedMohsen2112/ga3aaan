<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;
use App\Models\MenuSection;
use App\Models\MenuSectionTopping;
use App\Models\Resturant;
use App\Models\Order;
use App\Models\OrderMeal;
use App\Models\Meal;
use App\Models\Topping;
use DB;
use Validator;
use Session;
use App\Helpers\Fcm;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Traits\OrderTrait;

class ResturantOrdersController extends BackendController {

    use OrderTrait;

    public function __construct() {
        parent::__construct();
        $this->middleware('CheckPermission:resturant_orders,open', ['only' => ['index']]);
        $this->middleware('CheckPermission:resturant_orders,view', ['only' => ['show']]);
    }

    public function index(Request $request) {
        $this->data['type'] = $request->input('type');
        return $this->_view('resturant_orders/index', 'backend');
    }

    public function changeStatus(Request $request) {

        try {

            $order_id = decrypt($request->input('order_id'));

            //dd($this->User->resturant->id);
            $order = Order::join('users', 'users.id', '=', 'orders.user_id');
            $order->where('orders.id', $order_id);
            if ($this->User->type == 2) {
                $order->where('orders.resturant_id', $this->User->resturant->id);
            }
            $order->select('orders.id', 'users.device_token', 'users.device_type', 'orders.status');
            $order = $order->first();
            if ($order) {

                $status = $request->input('order_status');


                if ($status == 1) {
                    $body = 'تم الموافقة على طلبك لديك 3 دقائق فقط لتعديل طلبك';
                    $order->acceptance_date = date('Y-m-d H:i:s');
                } else if ($status == 4) {
                    $body = 'عذرًا ، لقد تم رفض طلبك';
                    $order->refusing_reason = $request->input('refusing_reason');
                } else {
                    if ($status == 2) {
                        $body = 'جارى توصيل طلبك';
                    } else if ($status == 3) {
                        $body = 'تم توصيل طلبك';
                    }
                }
                $order->status = $status;
                $order->save();
                $notification['title'] = "Ga3aaan";
                $notification['body'] = $body;
                $notification['type'] = $order->status;
                $notification['order_id'] = $order->id;
                $token = $order->device_token;
                $device_type = $order->device_type == 1 ? 'and' : 'ios';
                $Fcm = new Fcm;
                $Fcm->send($token, $notification, $device_type);
                return _json('success', _lang('app.updated_successfully'));
            } else {
                return _json('error', _lang('app.order_not_found'), 400);
            }
        } catch (DecryptException $e) {
            return _json('error', _lang('app.error_is_occured'), 400);
        } catch (\Exception $e) {
            return _json('error', $e->getMessage() . $e->getLine(), 400);
        }
    }

    public function data(Request $request) {
        $type = $request->input('order_type');
        $resturant_orders = Order::join('users', 'orders.user_id', '=', 'users.id');
        $resturant_orders->join('resturant_branches', 'orders.resturant_branch_id', '=', 'resturant_branches.id');
        $resturant_orders->join('resturantes', 'resturantes.id', '=', 'resturant_branches.resturant_id');
        $resturant_orders->join('addresses', 'addresses.id', '=', 'orders.user_address_id');
        $resturant_orders->where('orders.resturant_id', $this->User->resturant->id);
        if ($type == 1) {
            $resturant_orders->whereIn('orders.status', [0, 4, 5]);
        } else if ($type == 2) {
            $resturant_orders->whereIn('orders.status', [1, 2, 3, 6, 7]);
        }
        $resturant_orders = $resturant_orders->select([
                    'orders.id',
                    "resturant_branches.title_$this->lang_code as branch_title",
                    "orders.status", "users.first_name", "users.last_name", "users.mobile", "orders.created_at", 'addresses.city',
                    'addresses.region',
                    'addresses.sub_region',
                    'addresses.building_number',
                    'addresses.street',
                    'addresses.floor_number',
                    'addresses.apartment_number'
                ])
                ->orderBy('orders.created_at', 'desc');





        return \Datatables::eloquent($resturant_orders)
                        ->addColumn('options', function ($item) {

                            $back = "";
                            if (\Permissions::check('resturant_orders', 'view')) {

                                $back .= '<a class="btn btn-info" href="' . url('admin/resturant_orders/' . $item->id) . '">';
                                $back .= _lang('app.view');
                                $back .= '</a>';
                            }

                            return $back;
                        })
                        ->editColumn('status', function ($item) {

                            $back = _lang('app.' . $this->status_text[$item->status]);

                            return $back;
                        })
                        ->addColumn('name', function ($item) {

                            $back = $item->first_name . " " . $item->last_name;

                            return $back;
                        })
                        ->addColumn('address', function ($item) {

                            $back = $item->city . " " . $item->region . " - " . $item->sub_region . " - " . $item->building_number . " " . $item->street . " - " . _lang('app.floor') . " " . $item->floor_number . " - " . _lang('app.apartment') . " " . $item->apartment_number;

                            return $back;
                        })
                        ->filterColumn('name', function($query, $keyword) {
                            $query->whereRaw("CONCAT(users.first_name,' ',users.last_name) like ?", ["%{$keyword}%"]);
                        })
                        ->escapeColumns([])
                        ->make(true);
    }

}
