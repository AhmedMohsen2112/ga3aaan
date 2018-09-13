<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\FrontController;
use App\Events\updateOrderStatus;
use App\Models\Order;
use App\Models\OrderMeal;
use App\Models\Resturant;
use App\Models\ResturantBranch;
use App\Models\OrderMealTopping;
use Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use DB;
use App\Models\Rate;


class OrdersController extends FrontController {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
    }

    public function index(Request $request) {
        try {
            if (!in_array($request->type, ['current', 'completed'])) {
                return $this->err404();
            }
            $user = $this->User;
            $orders = Order::withTrashed();
            $orders->where('orders.user_id', $user->id);
            if ($request->type == 'current') {
                $this->data['page_title'] = _lang('app.current_orders');
                $orders->whereIn('status', [0, 1, 2]);
            } else {
                $this->data['page_title'] = _lang('app.completed_orders');
                $orders->whereIn('status', [3, 4]);
            }
            $orders->join('resturantes', 'orders.resturant_id', '=', 'resturantes.id');
            $orders->join('resturant_branches', 'resturant_branches.id', '=', 'orders.resturant_branch_id');
            $orders->join('cities', 'resturant_branches.region_id', '=', 'cities.id');
            $orders->select('orders.*', 'resturantes.title_' . $this->lang_code . ' as resturant', 'resturant_branches.slug as resturant_slug', 'resturantes.image', 'cities.title_' . $this->lang_code . ' as region');

            $orders->orderBy('orders.created_at', 'desc');
            $orders = $orders->paginate($this->limit);

            $orders->getCollection()->transform(function($order) {
                return Order::transformForPagination($order);
            });

            //$orders = Order::transformCollection($orders,'ForPagination');
            $this->data['orders'] = $orders;
            if ($request->type == 'current') {
                return $this->_view('orders.current');
            } else {
                return $this->_view('orders.completed');
            }
        } catch (\Exception $e) {
            //dd($e->getMessage());
            session()->flash('msg', _lang('app.error_is_occured'));
            //return redirect()->route('user_orders');
        }
    }

    public function edit2($id) {
        try {
            $order_id = decrypt($id);
            //$order_id = 64;

            $order = Order::join('addresses', 'orders.user_address_id', '=', 'addresses.id')
                    ->select('orders.id as order_id', 'orders.acceptance_date as acceptance_date', 'orders.vat', 'orders.primary_price', 'orders.total_cost', 'orders.net_cost', 'orders.toppings_price', 'orders.coupon', 'orders.service_charge', 'orders.delivery_cost', 'orders.resturant_id', 'orders.status', 'orders.is_rated', 'addresses.*')
                    ->where('orders.id', $order_id)
                    ->where('orders.user_id', $this->User->id)
                    ->first();
            if (!$order) {
                return $this->err404();
            }
        } catch (DecryptException $ex) {
            return $this->err404();
        }
        $order_meals = OrderMeal::where('order_id', $order_id)
                ->join('meals', 'order_meals.meal_id', '=', 'meals.id')
                ->leftJoin('meal_sizes', 'order_meals.meal_size_id', '=', 'meal_sizes.id')
                ->leftJoin('sizes', 'meal_sizes.size_id', '=', 'sizes.id')
                ->select('order_meals.*', 'meals.title_' . $this->lang_code . ' as meal_title', 'sizes.title_' . $this->lang_code . ' as size_title')
                ->get();
        $order_meals = OrderMeal::transformCollection($order_meals, 'EditOrder');

        $this->data['order'] = $order;
        $this->data['order_meals'] = $order_meals;
        return $this->_view('orders.edit');
    }

    public function updateOrderMealQuantity(Request $request) {
        //dd($request->all());
        $id = $request->input('id');
        $quantity = $request->input('qty');
        $OrderMeal = OrderMeal::find($id);
        if (!$OrderMeal) {
            
        }

        DB::beginTransaction();
        try {
            $Order = Order::find($OrderMeal->order_id);
            if (!$this->canEditOrder($Order)) {
                return _json('error', _lang('app.time_out_for_editing_order'));
            }
            $NewOrderMealCost = ($OrderMeal->cost_of_meal + $OrderMeal->toppings_price) * $quantity;
            $primary_price = ($Order->primary_price - $OrderMeal->cost_of_quantity) + $NewOrderMealCost;
            //dd($NewOrderMealCost);
            $PriceList['primary_price'] = $primary_price;
            $PriceList['vat_cost'] = (($PriceList['primary_price'] * $Order->vat) / 100);
            $PriceList['service_charge'] = (($PriceList['primary_price'] * $Order->service_charge) / 100);
            $PriceList['total_price'] = $PriceList['primary_price'] + $PriceList['vat_cost'] + $PriceList['service_charge'] + $Order->delivery_cost;

            $Order->primary_price = $PriceList['primary_price'];
            $Order->total_cost = $PriceList['total_price'];
            $Order->save();
            $OrderMeal->cost_of_quantity = $NewOrderMealCost;
            $OrderMeal->quantity = $quantity;
            $OrderMeal->save();
            DB::commit();
            return _json('success', $PriceList);
        } catch (\Exception $ex) {
            DB::rollback();
            return _json('error', $ex->getMessage());
        }
    }

    public function removeOrderMeal(Request $request) {
        //dd($request->all());
        $id = $request->input('id');
        $OrderMeal = OrderMeal::find($id);
        if (!$OrderMeal) {
            return _json('error', _lang('app.order_meal_is_not_found'));
        }

        DB::beginTransaction();
        try {
            $Order = Order::find($OrderMeal->order_id);
            if (!$this->canEditOrder($Order)) {
                return _json('error', _lang('app.time_out_for_editing_order'));
            }
            $OrderMealsCount = OrderMeal::where('order_id', $OrderMeal->order_id)->count();
            //dd($OrderMealsCount);
            if ($OrderMealsCount == 1) {
                $Order->delete();
                $response = _url('user-orders?type=current');
            } else {
                $primary_price = ($Order->primary_price - $OrderMeal->cost_of_quantity);
                //dd($NewOrderMealCost);
                $PriceList['primary_price'] = $primary_price;
                $PriceList['vat_cost'] = (($PriceList['primary_price'] * $Order->vat) / 100);
                $PriceList['service_charge'] = (($PriceList['primary_price'] * $Order->service_charge) / 100);
                $PriceList['total_price'] = $PriceList['primary_price'] + $PriceList['vat_cost'] + $PriceList['service_charge'] + $Order->delivery_cost;

                $Order->primary_price = $PriceList['primary_price'];
                $Order->total_cost = $PriceList['total_price'];
                $Order->save();
                $OrderMeal->delete();
                $response = $PriceList;
            }
            DB::commit();
            return _json('success', $response);
        } catch (\Exception $ex) {
            DB::rollback();
            return _json('error', $ex->getMessage());
        }
    }

    public function show($id) {
        $this->data['page_title'] = _lang('app.order_detailes');
        try {
            $order_id = decrypt($id);
            //$order_id = 64;
            //dd($order_id);
            $user = $this->User;

            $order = Order::withTrashed()->join('addresses', 'orders.user_address_id', '=', 'addresses.id')
                    ->select('orders.id as order_id', 'orders.net_cost', 'orders.acceptance_date as acceptance_date', 'orders.vat', 'orders.primary_price', 'orders.total_cost', 'orders.toppings_price', 'orders.coupon', 'orders.service_charge', 'orders.delivery_cost', 'orders.resturant_id', 'orders.status', 'orders.is_rated', 'orders.modified', 'addresses.*')
                    ->where('orders.id', $order_id)
                    ->where('orders.user_id', $user->id)
                    ->first();
            //dd($order);

            $order_meals = OrderMeal::withTrashed()->where('order_id', $order_id)
                    ->join('meals', 'order_meals.meal_id', '=', 'meals.id')
                    ->leftJoin('meal_sizes', 'order_meals.meal_size_id', '=', 'meal_sizes.id')
                    ->leftJoin('sizes', 'meal_sizes.size_id', '=', 'sizes.id')
                    ->select('order_meals.*', 'meals.title_' . $this->lang_code . ' as meal_title', 'sizes.title_' . $this->lang_code . ' as size_title')
                    ->get();

            if (!$order) {
                return $this->err404();
            }
            $order_time = strtotime($order->acceptance_date);
            $now_time = strtotime(date('Y-m-d H:i:s'));
            $minutes = ($now_time - $order_time) / 60;
            $this->data['order'] = $order;
            $this->data['order_meals'] = $order_meals;
            $this->data['minutes'] = $minutes;


            return $this->_view('orders.show');
        } catch (\Exception $e) {

            session()->flash('msg', _lang('app.error_is_occured'));
            return redirect()->route('user_orders');
        }
    }

    public function rate(Request $request) {
        DB::beginTransaction();
        try {
            $order_id = decrypt($request->order_id);
            $order = Order::find($order_id);

            if (!$order) {

                return $this->err404();
            }
            if (!$request->rate) {
                return redirect()->back();
            }
            $user = $this->User;
            $rate = new Rate;
            $rate->user_id = $user->id;
            $rate->resturant_branch_id = $order->resturant_branch_id;
            $rate->order_id = $order->id;
            $rate->rate = $request->rate;
            if ($request->opinion) {
                $rate->opinion = $request->opinion;
            }
            $rate->save();
            $order->is_rated = true;
            $order->save();

            $resturant_branch_rate = Rate::where('resturant_branch_id', $order->resturant_branch_id)
                    ->select(DB::raw(' SUM(rate)/COUNT(*) as rate'))
                    ->first();

            $resturant_branch = ResturantBranch::find($order->resturant_branch_id);
            $resturant_branch->rate = $resturant_branch_rate->rate;
            $resturant_branch->save();


            DB::commit();

            session()->flash('msg', _lang('app.rated_successfully'));
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('msg', _lang('app.error_is_occured_try_again_later'));
            return redirect()->back();
        }
    }

    public function destroy($id) {

        try {
            $order_id = decrypt($id);
            $Order = Order::find($order_id);

            if (!$Order) {
                return _json('error', _lang('app.order_is_not_found'));
            }
            if (!$this->canEditOrder($Order)) {
                return _json('error', _lang('app.time_out_for_cancelling_order'));
            }
            $Order->status = 6;
            $Order->save();

            $message = "order #" . $order_id . " cancelled from the client";
            event(new updateOrderStatus($Order->resturant_id, $message));
            return _json('success', _url('user-orders?type=current'));
        } catch (DecryptException $ex) {
            return _json('error', _lang('app.error_is_occured'));
        } catch (\Exception $ex) {
            return _json('error', _lang('app.error_is_occured'));
        }
    }

    private function canEditOrder($order) {

        $current_date = date('Y-m-d H:i:s');
        $acceptance_date = $order->acceptance_date;

        $to_time = strtotime($current_date);
        $from_time = strtotime($acceptance_date);

        $minutes_diff = round(abs($to_time - $from_time) / 60);

        if ($minutes_diff < $this->order_minutes_limit) {
            return true;
        }
        return false;
    }

    public function edit($id) {
        try {
            $order_id = decrypt($id);
            //$order_id = 64;

            $order = Order::join('resturant_branches', 'resturant_branches.id', '=', 'orders.resturant_branch_id')
                    ->join('resturantes', 'resturantes.id', '=', 'resturant_branches.resturant_id')
                    ->where('orders.id', $order_id)
                    ->select(['orders.*', 'resturant_branches.slug',
                        'resturant_branches.id as resturant_branch_id'])
                    ->first();
            if (!$order) {
                return $this->err404();
            }
        } catch (DecryptException $ex) {
            return $this->err404();
        }

        $cart['info'] = array(
            'order_id' => $order->id,
            'resturant_id' => $order->resturant_id,
            'resturant_slug' => $order->slug,
            'resturant_branch_id' => $order->resturant_branch_id,
            'service_charge' => $order->service_charge,
            'delivery_cost' => $order->delivery_cost,
            'vat' => $order->vat,
                // 'coupon' => $order->coupon_discount,
                //'coupon_cost' =>(($order->total_price * $order->coupon_discount) / 100),
        );
        $order->status = 5;
        $order->save();
        $cart['price_list']['primary_price'] = $order->primary_price;
        $cart['price_list']['toppings_price'] = $order->toppings_price;
        $cart['price_list']['vat_cost'] = (($order->primary_price * $order->vat) / 100);
        $cart['price_list']['service_charge'] = (($order->primary_price * $order->service_charge) / 100);
        $cart['price_list']['delivery_cost'] = $order->delivery_cost;
        $cart['price_list']['total_price'] = $order->total_cost;
        // $cart['price_list']['net_price'] = $order->net_cost;


        $cart['items'] = array();


        $order_meals = $this->getOrderMeals($order_id);


        foreach ($order_meals as $order_meal) {
            $meal = array(
                'id' => $order_meal->meal_id,
                'item_id' => $order_meal->meal_id_in_cart,
                'title_ar' => $order_meal->title_ar,
                'title_en' => $order_meal->title_en,
                'price' => $order_meal->cost_of_meal,
                'quantity' => $order_meal->quantity,
                'primary_price' => $order_meal->cost_of_meal * $order_meal->quantity,
                'toppings_price' => $order_meal->toppings_price,
                'total_price' => $order_meal->cost_of_quantity,
                'size_id' => null,
                'comment' => null,
                'toppings' => array(),
            );
            if ($order_meal->comment) {
                $meal['comment'] = $order_meal->comment;
            }
            if ($order_meal->meal_size_id) {
                $meal['size_id'] = $order_meal->meal_size_id;
                $meal['size_title_ar'] = $order_meal->size_title_ar;
                $meal['size_title_en'] = $order_meal->size_title_en;
            }
            $meal_toppings = $this->getOrderMealToppings($order_meal->id);
            //dd($meal_toppings);
            if (count($meal_toppings) > 0) {
                $meal['toppings'] = $meal_toppings;
            }
            $cart['items'][] = $meal;
        }
        $message = "Order #" . $order->id . " under client modification";
        event(new updateOrderStatus($order->resturant_id, $message));

        return redirect()->route('showcart')->withCookie(cookie('cart', serialize($cart)));
    }

    private function getOrderMeals($order_id) {
        $order_meals = OrderMeal::where('order_id', $order_id)
                ->join('meals', 'order_meals.meal_id', '=', 'meals.id')
                ->join('menu_sections', 'meals.menu_section_id', '=', 'menu_sections.id')
                ->leftJoin('meal_sizes', 'order_meals.meal_size_id', '=', 'meal_sizes.id')
                ->leftJoin('sizes', 'meal_sizes.size_id', '=', 'sizes.id')
                ->select(['order_meals.id', 'order_meals.meal_id_in_cart', 'order_meals.comment', 'order_meals.meal_id', 'order_meals.meal_size_id', 'order_meals.quantity',
                    'order_meals.cost_of_meal', 'order_meals.cost_of_quantity',
                    'menu_sections.id as menu_section_id', 'meals.title_ar', 'meals.title_en', 'sizes.title_ar as size_title_ar', 'sizes.title_en as size_title_en'])
                ->get();
        return $order_meals;
    }

    private function getOrderMealToppings($order_meal_id) {
        $order_meal_toppings = OrderMeal::leftJoin('order_meal_toppings', 'order_meal_toppings.order_meal_id', '=', 'order_meals.id')
                ->join('meal_toppings', 'order_meal_toppings.meal_topping_id', '=', 'meal_toppings.id')
                ->join('menu_section_toppings', 'meal_toppings.menu_section_topping_id', '=', 'menu_section_toppings.id')
                ->join('toppings', 'toppings.id', '=', 'menu_section_toppings.topping_id')
                ->where('order_meals.id', $order_meal_id)
                ->select('meal_toppings.id', 'order_meal_toppings.quantity', 'menu_section_toppings.price', 'toppings.title_ar', 'toppings.title_en')
                ->get();
        //return OrderMealTopping::transformCollection($order_meal_toppings);
        return $order_meal_toppings->toArray();
    }

}
