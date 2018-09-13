<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\FrontController;
use App\Models\Meal;
use App\Models\Topping;
use App\Models\PaymentMethod;
use App\Models\Coupon;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderMeal;
use App\Models\OrderMealTopping;
use Session;
use Validator;
use DB;

class CartControllerEx extends FrontController {

    private $cart = array();

    public function __construct() {
        parent::__construct();
        $this->middleware('auth', ['only' => ['new_order']]);
    }

    public function coupon_check(Request $request) {

        $cart = $this->getCart();

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
                    $cart['price_list']['total_price'] = $cart['price_list']['total_price'] - $discount;
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
                return $this->_view('cart.complete');
            } else {
                return redirect(_url('cart?step=1'));
            }
        }
        $cart = $this->getCart();
        //dd($cart);
        $this->data['cart'] = $cart;
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
        $index = $request->input('index');
        $qty = $request->input('qty');
        $cart = $this->getCart();
        if ($cart) {
            $items = $cart['items'];
            if (isset($cart['items'][$index])) {
                if ($qty == 0) {
                    unset($cart['items'][$index]);
                } else {
                    $cart['items'][$index]['quantity'] = $qty;
                    $cart['items'][$index]['total_price'] = $cart['items'][$index]['price'] * $qty;
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

    private function getCart() {
        $cart = \Cookie::get('cart');
        if (!$cart) {
            $cart = array(
                'info' => array(),
                'items' => array()
            );
        } else {
            $cart = unserialize($cart);
        }
        return $cart;
    }

    private function addToCart($request, $mealForCart) {
        //dd($request->all());

        $cart = $this->getCart();
        if (isset($cart['info']['resturant_branch_id']) && $cart['info']['resturant_branch_id'] != $request->resturant_branch_id) {
            \Cookie::forget('cart');
            $cart = array(
                'info' => array(),
                'items' => array()
            );
        }
        $items = $cart['items'];
        $new_items = array();

        //dd(count($cart));
        if (count($cart['items']) > 0) {
            foreach ($cart['items'] as $key => $one) {
                $exact = true;
                if ($one['size_id']) {

                    if ($one['id'] != $request->meal_id && $one['size_id'] != $request->size_id) {

                        $exact = false;
                    } else if ($one['id'] == $request->meal_id && $one['size_id'] != $request->size_id) {

                        $exact = false;
                    }
                } else {

                    if ($one['id'] !== $request->meal_id) {
                        $exact = false;
                    }
                }

                if ($exact) {
                    //if (count($one['toppings']) != 0 && count($request->toppings) != 0) {


                    if (count($one['toppings']) == count($request->toppings)) {
                        if (count($one['toppings']) > 0) {
                            foreach ($one['toppings'] as $topping) {
                                if (!in_array($topping['id'], array_values($request->toppings))) {
                                    $exact = false;
                                    break;
                                }
                            }
                        }
                    } else {
                        $exact = false;
                    }
                }

                if ($exact) {
                    //dd('here');
                    $one['quantity'] = $one['quantity'] + $request->quantity;
                    $one['total_price'] = ($request->quantity * $one['total_price']) + $one['total_price'];
                    $cart['items'][$key] = $one;
                    break;
                } else {
                    $exact = false;
                }
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
            $exact = false;
        }

        if (!$exact) {
            $cart['items'][] = $mealForCart;
        }
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
                $Order = $this->createOrder($this->User->id, $cart['info'], $cart['price_list']);
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
                return _json('success', _lang('app.request_sent_successfully'))->withCookie(\Cookie::forget('cart'));
            } catch (\Exception $ex) {
                DB::rollback();
                return _api_json('', ['message' => $ex->getMessage() . '' . $ex->getLine()], 422);
            }
        }
    }

    private function getPriceList($cart) {
        $primary_price = 0;
        if (count($cart['items']) > 0) {
            foreach ($cart['items'] as $one) {
                $primary_price += $one['total_price'];
            }
        }
        $PriceList['primary_price'] = $primary_price;
        $PriceList['vat_cost'] = (($PriceList['primary_price'] * $cart['info']['vat']) / 100);
        $PriceList['service_charge'] = (($PriceList['primary_price'] * $cart['info']['service_charge']) / 100);
        $PriceList['delivery_cost'] = $cart['info']['delivery_cost'];
        $PriceList['total_price'] = $PriceList['primary_price'] + $PriceList['vat_cost'] + $PriceList['service_charge'] + $PriceList['delivery_cost'];
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
            $meals->where('meal_sizes.id', $request->input('size_id'));
            $columns[] = 'meal_sizes.price';
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
        $data = array(
            'id' => $meal->id,
            'title_ar' => $meal->title_ar,
            'title_en' => $meal->title_ar,
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
                }
            }
        }
        $data['toppings_price'] = $toppings_price;
        $data['total_price'] = ($meal->price + $toppings_price) * $request->input('quantity');
        //$data['total_price'] = ($meal->price * $request->input('quantity'))+ $toppings_price;
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

        $newOrder->phone = 55555;

        if (isset($info['coupon'])) {
            $newOrder->coupon = $info['coupon'];
            $newOrder->coupon_discount = $price_list['coupon_cost'];
        }
        //$newOrder->net_cost = $order->order_info->net_cost;
        $newOrder->status = 0;
        $newOrder->commission = 0;
        $newOrder->date = date('Y-m-d');
        $newOrder->save();

        return $newOrder;
    }

    private function createOrderMeal($order_id, $meal) {


        $order_meal = new OrderMeal;
        $order_meal->order_id = $order_id;
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

}
