<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\FrontController;
use App\Models\Meal;
use App\Models\Topping;
use App\Models\PaymentMethod;
use App\Models\Coupon;
use App\Models\Address;
use App\Models\Resturant;
use App\Models\Order;
use App\Models\OrderMeal;
use App\Models\OrderMealTopping;
use App\Models\ResturantBranchDeliveryPlace;
use Session;
use Validator;
use DB;
use App\Events\updateOrderStatus;

class CartController extends FrontController {

    private $cart = array();

    public function __construct() {
        parent::__construct();
        $this->middleware('auth', ['only' => ['new_order']]);
    }

    public function coupon_check(Request $request) {

        $cart = $this->getCart();

        //dd($cart['info']['coupon']);
        if ($cart && !isset($cart['info']['coupon'])) {
            if (isset($cart['info']['resturant_id'])) {
                $coupon = $request->input('coupon');
                $Coupon = Coupon::join('resturantes', 'resturantes.id', '=', 'coupons.resturant_id')
                        ->where('resturantes.id', $cart['info']['resturant_id'])
                        ->where('coupons.available_until', '>', date('Y-m-d'))
                        ->where('coupons.coupon', $coupon)
                        ->select('coupons.id', "coupons.coupon", "coupons.discount")
                        ->first();
                if ($Coupon) {
                    //dd($cart);
                    $discount = (($cart['price_list']['total_price'] * $Coupon->discount) / 100);
                    $cart['price_list']['net_price'] = $cart['price_list']['total_price'] - $discount;
                    $cart['info']['coupon'] = $Coupon->coupon;
                    $cart['price_list']['coupon_cost'] = $discount;

                    return _json('success', $cart)->withCookie(cookie('cart', serialize($cart)));
                }
            }
            return _json('error', _lang('app.coupon_is_not_found'));
        } else {
            return _json('error', _lang('app.no_coupon_again'));
        }
    }

    public function emptyCart() {
        //dd('here');
        \Cookie::forget('cart');
        $cart = array(
            'info' => array(),
            'items' => array(),
            'price_list' => array()
        );
        return _json('success', $cart)->withCookie(cookie('cart', serialize($cart)));
    }

    public function index(Request $request) {

        $step = $request->input('step');
        if ($step && $step == 2) {

            $cart = $this->getCart();
            if ($cart && isset($cart['info']['resturant_id'])) {
                $payment_methods = PaymentMethod::join('resturant_payment_methods', 'payment_methods.id', '=', 'resturant_payment_methods.payment_method_id')
                        ->join('resturantes', 'resturantes.id', '=', 'resturant_payment_methods.resturant_id')
                        ->where('resturantes.id', $cart['info']['resturant_id'])
                        ->select('payment_methods.id', "payment_methods.title_$this->lang_code as title")
                        ->get();
                $addresses = Address::where('user_id', $this->User->id)
                        ->get();
                $addresses = Address::transformCollection($addresses);
                $this->data['payment_methods'] = $payment_methods;
                $this->data['addresses'] = $addresses;
                $this->data['cart'] = $cart;
                $this->data['page_title'] = _lang('app.complete_order');
                return $this->_view('cart.complete');
            } else {
                return redirect(_url('cart?step=1'));
            }
        }
        $cart = $this->getCart();
        //dd($cart);
        $this->data['cart'] = $cart;
        $this->data['page_title'] = _lang('app.cart');
        return $this->_view('cart.index');
    }

    public function store(Request $request) {

        $meal = $this->getMealForCart($request);
        if ($meal) {
            $cart = $this->addToCart($request, $meal);
            //dd($cart);
            $long = 7 * 60 * 24;

            return _json('success', _lang('app.added_successfully'))->withCookie(cookie('cart', serialize($cart)));
        }

        return _json('error', _lang('app.meal_is_not_found'));
    }

    public function update(Request $request, $id) {
        
    }

    public function remove($index) {
        $cart = $this->getCart();
        //dd($cart);
        if ($cart) {
            $items = $cart['items'];
            if (isset($cart['items'][$index])) {
                unset($cart['items'][$index]);
                //dd($cart['items']);
                if (count($cart['items']) > 0) {
                    $cart['price_list'] = $this->getPriceList($cart);
                    return _json('success', $cart)->withCookie(cookie('cart', serialize($cart)));
                } else {
                    //dd('here');
                    return _json('success', $cart)->withCookie(\Cookie::forget('cart'));
                }
            }
        }
        return _json('error', _lang('app.error_is_occured'));
    }

    public function update_quantity(Request $request) {
        $cart = $this->getCart();

        $index = $request->input('index');
        $qty = $request->input('qty');
        if ($cart) {
            $items = $cart['items'];
            if (isset($cart['items'][$index])) {
                if ($qty == 0) {
                    unset($cart['items'][$index]);
                } else {
                    $cart['items'][$index]['quantity'] = $qty;
                    $cart['items'][$index]['primary_price'] = $cart['items'][$index]['price'] * $qty;
                    $cart['items'][$index]['total_price'] = $cart['items'][$index]['primary_price'] * $cart['items'][$index]['toppings_price'];
                }
                if (count($cart['items']) > 0) {
                    $cart['price_list'] = $this->getPriceList($cart);
                    return _json('success', $cart)->withCookie(cookie('cart', serialize($cart)));
                } else {
                    //dd('here');
                    return _json('success', $cart)->withCookie(\Cookie::forget('cart'));
                }
            }
        }
        return _json('error', _lang('app.error_is_occured'));
    }

    private function addToCart($request, $mealForCart) {
        //dd($request->all());

        $cart = $this->getCart();
        if (isset($cart['info']['resturant_branch_id']) && $cart['info']['resturant_branch_id'] != $request->resturant_branch_id) {
            \Cookie::forget('cart');
            $cart = array(
                'info' => array(),
                'items' => array(),
                'price_list' => array()
            );
        }
        $items = $cart['items'];
        $new_items = array();
        if (count($cart['items']) > 0) {
            if (isset($cart['items'][$mealForCart['item_id']])) {
                $cart['items'][$mealForCart['item_id']]['quantity'] = $cart['items'][$mealForCart['item_id']]['quantity'] + $request->quantity;
                $cart['items'][$mealForCart['item_id']]['primary_price'] = $cart['items'][$mealForCart['item_id']]['price'] * $cart['items'][$mealForCart['item_id']]['quantity'];
                $cart['items'][$mealForCart['item_id']]['total_price'] = $cart['items'][$mealForCart['item_id']]['primary_price'] + $cart['items'][$mealForCart['item_id']]['toppings_price'];
            } else {
                $cart['items'][$mealForCart['item_id']] = $mealForCart;
            }
        } else {
            $cart['info'] = array(
                'resturant_id' => $request->resturant_id,
                'resturant_slug' => $request->resturant_slug,
                'resturant_branch_id' => $request->resturant_branch_id,
                'service_charge' => $request->service_charge,
                'delivery_cost' => $request->delivery_cost,
                'vat' => $request->vat
            );
            $cart['items'][$mealForCart['item_id']] = $mealForCart;
        }
        //dd($cart);
        $cart['price_list'] = $this->getPriceList($cart);
        return $cart;
    }

    public function new_order(Request $request) {
        //return _json('success', _lang('app.request_sent_successfully'));
        $rules = array(
            'address' => 'required',
            'payment_method' => 'required'
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            if ($request->ajax()) {
                return _json('error', $errors);
            } else {
                return redirect()->back()->withInput($request->all())->withErrors($errors);
            }
        } else {
            $cart = $this->getCart();
            //dd($cart);
            DB::beginTransaction();
            try {
                $address = $request->input('address');
                $payment_method = $request->input('payment_method');
                $cart['info']['address'] = $address;
                $cart['info']['payment_method'] = $payment_method;
                //dd($cart);
                if (isset($cart['info']['order_id'])) {
                    $Order = $this->updateOrder($this->User->id, $cart['info'], $cart['price_list']);
                      $message = "Order #".$Order->id." modified successfully";
                    foreach ($Order->order_meals as $item) {
                        $item->forceDelete();
                    }
                } else {
                    $Order = $this->createOrder($this->User->id, $cart['info'], $cart['price_list']);
                     $message = "New order has been received #".$Order->id;
                }

                foreach ($cart['items'] as $item) {
                    //dd($Order);
                    $order_meal = $this->createOrderMeal($Order->id, $item);
                    if (count($item['toppings']) > 0) {
                        foreach ($item['toppings'] as $topping) {
                            $this->createOrderTopping($order_meal->id, $topping);
                        }
                    }
                }
                DB::commit();
               
                event(new updateOrderStatus($cart['info']['resturant_id'],$message));
                return _json('success', _lang('app.request_sent_successfully'))->withCookie(\Cookie::forget('cart'));
            } catch (\Exception $ex) {
                DB::rollback();
                return _api_json('', ['message' => $ex->getMessage() . '' . $ex->getLine()], 422);
            }
        }
    }

    private function getPriceList($cart) {
        $primary_price = 0;
        $toppings_price = 0;


        //dd($cart);
        if (count($cart['items']) > 0) {
            foreach ($cart['items'] as $one) {
                $primary_price += $one['primary_price'];
                $toppings_price += $one['toppings_price'];
            }
        }
        $PriceList['primary_price'] = $primary_price;
        $PriceList['toppings_price'] = $toppings_price;
        $PriceList['vat_cost'] = (($PriceList['primary_price'] * $cart['info']['vat']) / 100);
        $PriceList['service_charge'] = (($PriceList['primary_price'] * $cart['info']['service_charge']) / 100);
        $PriceList['delivery_cost'] = $cart['info']['delivery_cost'];
        $PriceList['total_price'] = $PriceList['primary_price'] + $PriceList['toppings_price'] + $PriceList['vat_cost'] + $PriceList['service_charge'] + $PriceList['delivery_cost'];
        return $PriceList;
    }

    private function getPriceList2($cart) {
        $primary_price = 0;
        if (count($cart['items']) > 0) {
            foreach ($cart['items'] as $one) {
                $primary_price += $one['total_price'];
            }
        }
        $PriceList['primary_price'] = $primary_price;
        $PriceList['service_charge'] = $cart['info']['service_charge'];
        $total_price = $PriceList['primary_price'] + $PriceList['service_charge'];
        $PriceList['vat_cost'] = $total_price - (($total_price * $cart['info']['vat']) / 100);
        $PriceList['delivery_cost'] = $cart['info']['delivery_cost'];
        $PriceList['total_price'] = $total_price + $PriceList['vat_cost'] + $PriceList['delivery_cost'];
        return $PriceList;
    }

    private function getMealForCart($request) {
        //dd($request->all());
        $columns = array('meals.*');
        $meals = Meal::join('menu_sections', 'menu_sections.id', '=', 'meals.menu_section_id');
        $meals->join('resturantes', 'resturantes.id', '=', 'menu_sections.resturant_id');
        $meals->where('resturantes.id', $request->input('resturant_id'));
        $meals->where('meals.id', $request->input('meal_id'));
        if ($request->input('size_id')) {
            $meals->join('meal_sizes', 'meal_sizes.meal_id', '=', 'meals.id');
            $meals->join('sizes', 'sizes.id', '=', 'meal_sizes.size_id');
            $meals->where('meal_sizes.id', $request->input('size_id'));
            $columns[] = 'meal_sizes.price';
            $columns[] = 'sizes.title_ar as size_title_ar';
            $columns[] = 'sizes.title_en as size_title_en';
        } else {
            $columns[] = 'meals.price';
        }
        $meals->select($columns);
        $meal = $meals->first();

        if ($meal) {
            $discount = Meal::getDiscount(Meal::find($meal->id));
            if ($discount != 0) {
                $meal->price = $meal->price - (($meal->price * $discount) / 100);
            }
            $meal = $this->formatMealForCart($request, $meal);
        }
        return $meal;
    }

    private function formatMealForCart($request, $meal) {
        $toppings_price = 0;
        $item_id = $meal->id;
        $data = array(
            'id' => $meal->id,
            'title_ar' => $meal->title_ar,
            'title_en' => $meal->title_en,
            'price' => $meal->price,
            'quantity' => $request->input('quantity'),
            'size_id' => null,
            'comment' => null,
            'toppings' => array(),
        );
        if ($request->input('comment')) {
            $data['comment'] = $request->input('comment');
        }
        if ($request->input('size_id')) {
            $data['size_id'] = $request->input('size_id');
            $data['size_title_ar'] = $meal->size_title_ar;
            $data['size_title_en'] = $meal->size_title_en;
            $item_id .= $data['size_id'];
        }
        if ($request->input('toppings')) {
            $toppings = $request->input('toppings');
            $tqty = $request->input('tqty');
            $toppings = $this->getMealToppings($toppings);
            if ($toppings->count() > 0) {
                foreach ($toppings as $topping) {
                    $data['toppings'][] = array(
                        'id' => $topping->id,
                        'title_ar' => $topping->title_ar,
                        'title_en' => $topping->title_ar,
                        'price' => $topping->price,
                        'quantity' => $tqty[$topping->id]
                    );
                    $toppings_price += $topping->price * $tqty[$topping->id];
                    $item_id .= $topping->id . $tqty[$topping->id];
                }
            }
        }
        $data['item_id'] = $item_id;
        $data['toppings_price'] = $toppings_price;
        $data['primary_price'] = $meal->price * $request->input('quantity');
        $data['total_price'] = $data['primary_price'] + $toppings_price;
        return $data;
    }

    private function getMealToppings($toppings) {
        //dd($toppings);
        $Toppings = Topping::join('menu_section_toppings', 'toppings.id', '=', 'menu_section_toppings.topping_id');
        $Toppings->join('meal_toppings', 'menu_section_toppings.id', '=', 'meal_toppings.menu_section_topping_id');
//        $Toppings->join('menu_sections', 'menu_sections.id', '=', 'menu_section_toppings.menu_section_id');
//        $Toppings->join('meals', 'menu_sections.id', '=', 'meals.menu_section_id');
        $Toppings->select('meal_toppings.id', 'toppings.title_ar', 'toppings.title_en', 'menu_section_toppings.price');
        $Toppings->whereIn('meal_toppings.id', $toppings);
        $result = $Toppings->get();
        return $result;
    }

    private function createOrder($user_id, $info, $price_list) {
        $resturant = Resturant::find($info['resturant_id']);
        $newOrder = new Order;
        $newOrder->user_id = $user_id;
        $newOrder->resturant_id = $info['resturant_id'];
        $newOrder->resturant_branch_id = $info['resturant_branch_id'];
        $newOrder->user_address_id = $info['address'];
        $newOrder->payment_method_id = $info['payment_method'];

        $newOrder->primary_price = $price_list['primary_price'];
        $newOrder->service_charge = $info['service_charge'];
        $newOrder->vat = $info['vat'];
        $newOrder->delivery_cost = $price_list['delivery_cost'];
        $newOrder->total_cost = $price_list['total_price'];
        $newOrder->toppings_price = $price_list['toppings_price'];

        $newOrder->phone = '';

        if (isset($info['coupon'])) {
            $newOrder->coupon = $info['coupon'];
            $newOrder->coupon_discount = $price_list['coupon_cost'];
            $newOrder->net_cost = $price_list['net_price'];
        } else {
            $newOrder->net_cost = $price_list['total_price'];
        }

        $newOrder->status = 0;
        $newOrder->commission = $resturant->commission;
        $newOrder->date = date('Y-m-d');
        $newOrder->save();

        return $newOrder;
    }

    private function updateOrder($user_id, $info, $price_list) {
        $resturant = Resturant::find($info['resturant_id']);
        $newOrder = Order::find($info['order_id']);
        $newOrder->user_id = $user_id;
        $newOrder->resturant_id = $info['resturant_id'];
        $newOrder->resturant_branch_id = $info['resturant_branch_id'];
        $newOrder->user_address_id = $info['address'];
        $newOrder->payment_method_id = $info['payment_method'];

        $newOrder->primary_price = $price_list['primary_price'];
        $newOrder->service_charge = $info['service_charge'];
        $newOrder->vat = $info['vat'];
        $newOrder->delivery_cost = $price_list['delivery_cost'];
        $newOrder->total_cost = $price_list['total_price'];
        $newOrder->toppings_price = $price_list['toppings_price'];

        $newOrder->phone = '';

        if (isset($info['coupon'])) {
            $newOrder->coupon = $info['coupon'];
            $newOrder->coupon_discount = $price_list['coupon_cost'];
            $newOrder->net_cost = $price_list['net_price'];
        } else {
            $newOrder->net_cost = $price_list['total_price'];
        }

        $newOrder->status = 1;
        $newOrder->modified = 1;
        $newOrder->commission = $resturant->commission;
        $newOrder->date = date('Y-m-d');
        $newOrder->save();

        return $newOrder;
    }

    private function createOrderMeal($order_id, $meal) {


        $order_meal = new OrderMeal;
        $order_meal->order_id = $order_id;
        $order_meal->meal_id_in_cart = $meal['item_id'];
        $order_meal->meal_id = $meal['id'];
        $order_meal->meal_size_id = $meal['size_id'];
        $order_meal->quantity = $meal['quantity'];
        $order_meal->cost_of_meal = $meal['price'];
        $order_meal->cost_of_quantity = $meal['total_price'];
        $order_meal->toppings_price = $meal['toppings_price'];

        $order_meal->comment = $meal['comment'];

        $order_meal->save();

        return $order_meal;
    }

    private function createOrderTopping($order_meal_id, $topping) {

        $order_meal_topping = new OrderMealTopping;
        $order_meal_topping->order_meal_id = $order_meal_id;
        $order_meal_topping->meal_topping_id = $topping['id'];
        $order_meal_topping->quantity = $topping['quantity'];
        $order_meal_topping->cost_of_topping = $topping['price'];
//        $order_meal_topping->cost_of_quantity = $topping['total_price'];

        $order_meal_topping->save();
    }

    public function resendOrder(Request $request) {

        try {
            $area_id = \Cookie::get('area_id');
            if (!$area_id) {
                if ($request->ajax()) {
                    return _json('error', _lang('app.please_select_your_region'));
                }

                return redirect()->back()->with(['errorMessage' => _lang('app.please_select_your_region')]);
            }
            $user = $this->User;
            $order = Order::withTrashed()->where('orders.id', $request->order_id)
                    ->leftJoin('offers', function ($join) use($request) {
                        $join->on('orders.resturant_id', '=', 'offers.resturant_id')
                        ->where('offers.resturant_id', $request->resturant_id)
                        ->where('offers.available_until', '>=', date('Y-m-d'));
                    })
                    ->join('resturantes', 'orders.resturant_id', '=', 'resturantes.id')
                    ->join('resturant_branches', 'orders.resturant_branch_id', '=', 'resturant_branches.id')
                    ->select('orders.*', 'offers.id as offer_id', 'offers.discount', 'offers.type', 'offers.menu_section_ids', 'resturantes.vat', 'resturantes.service_charge', 'resturantes.commission', 'resturant_branches.active as branch_status', 'resturantes.active as resturant_status', 'resturantes.available', 'resturantes.working_hours')
                    ->first();


            if ($order) {
                $check_branch_delivery = ResturantBranchDeliveryPlace::join('resturant_branches', 'resturant_branches.id', '=', 'branch_delivery_places.resturant_branch_id')
                        ->where('branch_delivery_places.resturant_branch_id', $order->resturant_branch_id)
                        ->where('branch_delivery_places.region_id', $area_id)
                        ->select('resturant_branches.slug', 'branch_delivery_places.delivery_cost')
                        ->first();
                if ($check_branch_delivery) {
                    $new_delivery_cost = $check_branch_delivery->delivery_cost;
                } else {

                    if ($request->ajax()) {
                        return _json('error', _lang('app.sorry_,this_reaturant_doesn\'t_deliver_to_your_address'));
                    }

                    return redirect()->back()->with(['errorMessage' => _lang('app.sorry_,this_reaturant_doesn\'t_deliver_to_your_address')]);
                }
            }

            if (
                    $order->branch_status == false ||
                    $order->resturant_status == false ||
                    $order->available == false ||
                    Resturant::checkIsOpen(json_decode($order->working_hours)) == false
            ) {

                if ($request->ajax()) {
                    return _json('error', _lang('app.sorry_this_resturant_is_not_available_now'));
                }

                return redirect()->back()->with(['errorMessage' => _lang('app.sorry_this_resturant_is_not_available_now')]);
            }




            $order_meals = OrderMeal::withTrashed()->where('order_id', $request->order_id)
                    ->join('meals', 'order_meals.meal_id', '=', 'meals.id')
                    ->join('menu_sections', 'meals.menu_section_id', '=', 'menu_sections.id')
                    ->leftJoin('meal_sizes', 'order_meals.meal_size_id', '=', 'meal_sizes.id')
                    ->leftJoin('sizes', 'meal_sizes.size_id', '=', 'sizes.id')
                    ->select(
                            'order_meals.id', 'order_meals.comment', 'order_meals.meal_id', 'order_meals.meal_size_id', 'order_meals.quantity', 'meals.price as meal_price', 'meal_sizes.price as size_price', 'menu_sections.id as menu_section_id', 'meals.title_ar', 'meals.title_en', 'sizes.title_ar as size_title_ar', 'sizes.title_en as size_title_en'
                    )
                    ->get();


            foreach ($order_meals as $order_meal) {

                $order_meal_toppings = OrderMeal::withTrashed()->
                        leftJoin('order_meal_toppings', 'order_meal_toppings.order_meal_id', '=', 'order_meals.id')
                        ->join('meal_toppings', 'order_meal_toppings.meal_topping_id', '=', 'meal_toppings.id')
                        ->join('menu_section_toppings', 'meal_toppings.menu_section_topping_id', '=', 'menu_section_toppings.id')
                        ->where('order_meal_toppings.order_meal_id', $order_meal->id)
                        ->select('order_meal_toppings.meal_topping_id', 'order_meal_toppings.quantity', 'menu_section_toppings.price')
                        ->get();

                if (!empty($order_meal_toppings)) {
                    foreach ($order_meal_toppings as $value) {
                        $order_meal->toppings_price += $value->price * $value->quantity;
                    }
                } else {
                    $order_meal->toppings_price = 0;
                }

                $discount = 0;

                if ($order->offer_id) {
                    $discount = $this->getDiscount($order_meal->menu_section_id, $order->offer_id, $order->type, $order->discount, $order->menu_section_ids);
                }

                $order_meal->cost_of_meal = $order_meal->size_price ? $order_meal->size_price : $order_meal->meal_price;

                $order_meal->cost_of_meal = $order_meal->cost_of_meal - (($order_meal->cost_of_meal * $discount) / 100);

                $order_meal->cost_of_quantity = ($order_meal->cost_of_meal * $order_meal->quantity) + $order_meal->toppings_price;

                $order_meal->topppings = $order_meal_toppings;
            }

            $new_primary_price = 0;
            $new_toppings_price = 0;

            foreach ($order_meals as $order_meal) {
                $new_primary_price += $order_meal->cost_of_quantity;
                $new_toppings_price += $order_meal->toppings_price;
            }

            $new_vat = $order->vat;
            $new_commission = $order->commission;
            $new_service_charge = $order->service_charge;


            $new_total_cost = $new_primary_price + $new_toppings_price + $new_delivery_cost + (($new_primary_price * $new_vat) / 100) + (($new_primary_price * $new_service_charge) / 100);

            $meals = array();


            foreach ($order_meals as $order_meal) {
                $item_id = $order_meal->meal_id;
                $meal = array(
                    'id' => $order_meal->meal_id,
                    'title_ar' => $order_meal->title_ar,
                    'title_en' => $order_meal->title_en,
                    'price' => $order_meal->cost_of_meal,
                    'quantity' => $order_meal->quantity,
                    'size_id' => null,
                    'comment' => null,
                    'toppings' => array(),
                );

                if ($order_meal->comment) {
                    $meal['comment'] = $order_meal->comment;
                }
                if ($order_meal->size_id) {

                    $meal['size_id'] = $order_meal->meal_size_id;
                    $meal['size_title_ar'] = $order_meal->size_title_ar;
                    $meal['size_title_en'] = $order_meal->size_title_en;
                    $item_id .= $meal['size_id'];
                }
                if ($order_meal->topppings) {
                    foreach ($order_meal->topppings as $meal_topping) {
                        $meal['toppings'][] = array(
                            'id' => $meal_topping->meal_topping_id,
                            'price' => $meal_topping->price,
                            'quantity' => $meal_topping->quantity
                        );
                        $item_id .= $meal_topping->meal_topping_id . $meal_topping->quantity;
                    }
                }
                $meal['item_id'] = $item_id;
                $meal['toppings_price'] = $order_meal->toppings_price;
                $meal['primary_price'] = $order_meal->price * $order_meal->quantity;
                $meal['total_price'] = $meal['primary_price'] + $meal['toppings_price'];

                array_push($meals, $meal);
            }

            $cart['items'] = $meals;

            $cart['info'] = array(
                'resturant_id' => $order->resturant_id,
                'resturant_slug' => $check_branch_delivery->slug,
                'resturant_branch_id' => $order->resturant_branch_id,
                'service_charge' => $new_service_charge,
                'delivery_cost' => $new_delivery_cost,
                'vat' => $new_vat
            );

            $PriceList['primary_price'] = $new_primary_price;
            $PriceList['toppings_price'] = $new_toppings_price;
            $PriceList['vat_cost'] = (($PriceList['primary_price'] * $cart['info']['vat']) / 100);
            $PriceList['service_charge'] = (($PriceList['primary_price'] * $cart['info']['service_charge']) / 100);
            $PriceList['delivery_cost'] = $new_delivery_cost;
            $PriceList['total_price'] = $new_total_cost;

            $cart['price_list'] = $PriceList;
            return redirect()->route('showcart')->withCookie(cookie('cart', serialize($cart)));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return _json('error', _lang('app.error_is_occured'));
            }
            return redirect()->back()->with(['errorMessage' => _lang('app.error_is_occured')]);
        }
    }

    private function getDiscount($meal_menu_section, $offer, $type, $discount, $menu_sections) {

        $offer_discount = 0;
        if ($offer) {
            if ($type == 1) {
                $offer_discount = $discount;
            } elseif ($type == 2) {
                $menu_sections = explode(",", $menu_sections);
                if (!in_array($meal_menu_section, $menu_sections)) {
                    $offer_discount = $discount;
                }
            } elseif ($type == 3) {
                $menu_sections = explode(",", $menu_sections);
                if (in_array($meal_menu_section, $menu_sections)) {
                    $offer_discount = $discount;
                }
            }
        }
        return $offer_discount;
    }

}
