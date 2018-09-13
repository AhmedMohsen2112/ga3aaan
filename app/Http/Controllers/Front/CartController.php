<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\FrontController;
use App\Models\Meal;
use App\Models\SubChoice;
use App\Models\Topping;
use App\Models\PaymentMethod;
use App\Models\Coupon;
use App\Models\Address;
use App\Models\Resturant;
use App\Models\Order;
use App\Models\OrderMeal;
use App\Models\OrderMealChoice;
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
                $Coupon = Coupon::where(function ($query) use($cart) {
                            $query->where(function ($query2) use($cart) {
                                $query2->where('resturant_id', $cart['info']['resturant_id']);
                                $query2->where('resturant_branch_id', $cart['info']['resturant_branch_id']);
                            });
                            $query->orWhere('resturant_id', null);
                        })
                        ->where('available_until', '>', date('Y-m-d'))
                        ->where('coupon', $coupon)
                        ->select('id', "coupon", "discount")
                        ->first();
                if ($Coupon) {
                    //dd($cart);
                    $discount = (($cart['price_list']['total_price'] * $Coupon->discount) / 100);
                    $cart['price_list']['net_price'] = $cart['price_list']['total_price'] - $discount;
                    $cart['info']['coupon'] = $Coupon->coupon;
                    $cart['info']['coupon_discount'] = $Coupon->discount;
                    $cart['price_list']['coupon_cost'] = $discount;
//                      $long = 7 * 60 * 24;
//                    \Cookie::queue(\Cookie::forget('cart'));
//                    \Cookie::queue(\Cookie::make('cart', serialize($cart),$long));
//                    return _json('success', $cart);
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
            //dd($cart);
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
                //dd($cart);
                return $this->_view('cart.complete');
            } else {
                return redirect(_url('cart?step=1'));
            }
        }
        $cart = $this->getCart();
        //dd($cart);
        \Cookie::queue(\Cookie::make('cart', serialize($cart)));
        $this->data['cart'] = $cart;
        $this->data['page_title'] = _lang('app.cart');
        return $this->_view('cart.index');
    }

    public function store(Request $request) {
        $choices_errors = $this->choices_errors($request);
        if (count($choices_errors) > 0) {
            return _json('error', $choices_errors);
        }
        $meal = $this->getMealForCart($request);
        //dd($meal);
        if ($meal) {
            $cart = $this->addToCart($request, $meal);
            //dd($cart);
            $long = 7 * 60 * 24;

            return _json('success', _lang('app.added_successfully'))->withCookie(cookie('cart', serialize($cart)));
        }

        return _json('error', _lang('app.meal_is_not_found'));
    }

    public function choices_errors($request) {
        $errors = [];
        $meal_id = $request->input('meal_id');
        $selected_choices = $request->input('choices');
        if ($request->input('size_id')) {
            $where_arr['meal_size_id'] = $request->input('size_id');
        } else {
            $where_arr['meal_id'] = $request->input('meal_id');
        }
        $choices = Meal::getMealSizeChoices($where_arr);
        if (count($choices) > 0) {
            //dd($choices);
            foreach ($choices as $choice) {
                $html_id = 'ch' . $choice->id;
                $min = $choice->min;
                $max = $choice->max;
                if ($min == 0 && $max > 0) {
                    //dd($selected_choices);
                    if (isset($selected_choices[$choice->id])) {
                        if (count($selected_choices[$choice->id]) > $max) {
                            $message = _lang('app.maximum_choices_is_') . $max;
                            $errors[$html_id] = $message;
                        }
                    }
                } else if ($min > 0 && $max == 0) {

                    $message = _lang('app.minimum_choices_is_') . $min;
                    if (isset($selected_choices[$choice->id])) {
                        if (count($selected_choices[$choice->id]) < $min) {
                            $errors[$html_id] = $message;
                        }
                    } else {
                        $errors[$html_id] = $message;
                    }
                } else if ($min > 0 && $max > 0) {
                    if (isset($selected_choices[$choice->id])) {
                        if (count($selected_choices[$choice->id]) < $min) {
                            $message = _lang('app.minimum_choices_is_') . $min;
                            $errors[$html_id] = $message;
                        } else if (count($selected_choices[$choice->id]) > $max) {
                            $message = _lang('app.maximum_choices_is_') . $max;
                            $errors[$html_id] = $message;
                        }
                    } else {
                        $message = _lang('app.minimum_choices_is_') . $min;
                        $errors[$html_id] = $message;
                    }
                } else {
                    continue;
                }
            }
        }
        //dd($errors);
        return $errors;
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
                    $cart['items'][$index]['total_price'] = $cart['items'][$index]['primary_price'] * $cart['items'][$index]['quantity'];
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
                $cart['items'][$mealForCart['item_id']]['total_price'] = $cart['items'][$mealForCart['item_id']]['primary_price'] * $cart['items'][$mealForCart['item_id']]['quantity'];
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
                    $message = "Order #" . $Order->id . " modified successfully";
                    foreach ($Order->order_meals as $item) {
                        $item->forceDelete();
                    }
                } else {
                    $Order = $this->createOrder($this->User->id, $cart['info'], $cart['price_list']);
                    $message = "New order has been received #" . $Order->id;
                }

                foreach ($cart['items'] as $item) {
                    //dd($Order);
                    $order_meal = $this->createOrderMeal($Order->id, $item);
                    if (count($item['sub_choices']) > 0) {
                        //dd($item['sub_choices']);
                        foreach ($item['sub_choices'] as $sub_choice) {
                            $this->createOrderChoice($order_meal->id, $sub_choice);
                        }
                    }
                }
                DB::commit();

                event(new updateOrderStatus($cart['info']['resturant_id'], $message));
                return _json('success', _lang('app.request_sent_successfully'))->withCookie(\Cookie::forget('cart'));
            } catch (\Exception $ex) {
                DB::rollback();
                return _api_json('', ['message' => $ex->getMessage() . '' . $ex->getLine()], 422);
            }
        }
    }

    private function getPriceList($cart) {
        $total_price = 0;


        //dd($cart);
        if (count($cart['items']) > 0) {
            foreach ($cart['items'] as $one) {
                $total_price += $one['total_price'];
            }
        }
        $PriceList['primary_price'] = $total_price;
        $PriceList['vat_cost'] = (($PriceList['primary_price'] * $cart['info']['vat']) / 100);
        $PriceList['service_charge'] = (($PriceList['primary_price'] * $cart['info']['service_charge']) / 100);
        $PriceList['delivery_cost'] = $cart['info']['delivery_cost'];
        $PriceList['total_price'] = $PriceList['primary_price'] + $PriceList['vat_cost'] + $PriceList['service_charge'] + $PriceList['delivery_cost'];
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
        $sub_choices_price = 0;
        $item_id = $meal->id;
        $data = array(
            'id' => $meal->id,
            'title_ar' => $meal->title_ar,
            'title_en' => $meal->title_en,
            'price' => $meal->price,
            'quantity' => $request->input('quantity'),
            'size_id' => null,
            'comment' => null,
            'sub_choices' => array(),
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
        if ($choices = $request->input('choices')) {
            $sub_choices_arr = [];
            if (count($choices) > 0) {
                foreach ($choices as $choice => $sub_choice) {
                    $sub_choices_arr = array_merge($sub_choices_arr, $sub_choice);
                }
            }
            $sub_choices = $this->getMealSubChoices($sub_choices_arr);

            if ($sub_choices->count() > 0) {
                foreach ($sub_choices as $one) {
                    $data['sub_choices'][] = array(
                        'id' => $one->id,
                        'title_ar' => $one->title_ar,
                        'title_en' => $one->title_ar,
                        'price' => $one->price,
                    );
                    $sub_choices_price += $one->price;
                    $item_id .= $one->id;
                }
            }
        }
        $data['item_id'] = $item_id;
        $data['sub_choices_price'] = $sub_choices_price;
        $data['primary_price'] = $meal->price + $sub_choices_price;
        $data['total_price'] = $data['primary_price'] * $request->input('quantity');
        return $data;
    }

    private function getMealSubChoices($sub_choices_arr) {
        //dd($toppings);
        $sub_choices = SubChoice::whereIn('id', $sub_choices_arr)->get();
        return $sub_choices;
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

        $newOrder->phone = '';

        if (isset($info['coupon'])) {
            $newOrder->coupon = $info['coupon'];
            $newOrder->coupon_discount = $info['coupon_discount'];
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

        $newOrder->phone = '';

        if (isset($info['coupon'])) {
            $newOrder->coupon = $info['coupon'];
            $newOrder->coupon_discount = $info['coupon_discount'];
            $newOrder->net_cost = $price_list['net_price'];
        } else {
            $newOrder->net_cost = $price_list['total_price'];
        }

        $newOrder->status = 7;
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
        $order_meal->sub_choices_price = $meal['sub_choices_price'];
        $order_meal->comment = $meal['comment'];

        $order_meal->save();

        return $order_meal;
    }

    private function createOrderChoice($order_meal_id, $sub_choice) {

        $OrderMealChoice = new OrderMealChoice;
        $OrderMealChoice->order_meal_id = $order_meal_id;
        $OrderMealChoice->sub_choice_id = $sub_choice['id'];
        $OrderMealChoice->price = $sub_choice['price'];

        $OrderMealChoice->save();
    }

    public function resendOrder(Request $request) {

        try {
            $area_id = \Cookie::get('area_id');
            if (!$area_id) {
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
                    return redirect()->back()->with(['errorMessage' => _lang('app.sorry_,this_reaturant_doesn\'t_deliver_to_your_address')]);
                }
            }

            if ($order->branch_status == false || $order->resturant_status == false || $order->available == false || Resturant::checkIsOpen(json_decode($order->working_hours)) == false) {
                return redirect()->back()->with(['errorMessage' => _lang('app.sorry_this_resturant_is_not_available_now')]);
            }
            $cart['info'] = array();
            $cart['price_list'] = array();
            $cart['items'] = array();

            $order_primary_price = 0;
            $order_meals = Order::getOrderMeals($order->id);



            foreach ($order_meals as $order_meal) {
                $item_id = $order_meal->meal_id;
                $meal_total_price = 0;
                $sub_choices_price = 0;
                $discount = 0;
                if ($order->offer_id) {
                    $discount = $this->getDiscount($order_meal->menu_section_id, $order->offer_id, $order->type, $order->discount, $order->menu_section_ids);
                }
                $meal = array(
                    'id' => $order_meal->meal_id,
                    'item_id' => $order_meal->meal_id_in_cart,
                    'title_ar' => $order_meal->title_ar,
                    'title_en' => $order_meal->title_en,
                    'quantity' => $order_meal->quantity,
                    'size_id' => null,
                    'comment' => null,
                    'sub_choices' => array(),
                );
                $meal['price'] = $order_meal->size_price ? $order_meal->size_price : $order_meal->meal_price;
                $meal['price'] = $meal['price'] - (( $meal['price'] * $discount) / 100);
                if ($order_meal->comment) {
                    $meal['comment'] = $order_meal->comment;
                }
                if ($order_meal->meal_size_id) {

                    $meal['size_id'] = $order_meal->meal_size_id;
                    $meal['size_title_ar'] = $order_meal->size_title_ar;
                    $meal['size_title_en'] = $order_meal->size_title_en;
                    $item_id .= $meal['size_id'];
                }
                $meal_total_price += $meal['price'];

                $meal_choices = Order::getOrderMealChoices($order_meal->id);
                if ($meal_choices->count() > 0) {
                    foreach ($meal_choices as $meal_choice) {
                        $choice = array();
                        $choice['id'] = $meal_choice->id;
                        $choice['title_ar'] = $meal_choice->title_ar;
                        $choice['title_en'] = $meal_choice->title_en;
                        $choice['price'] = $meal_choice->price;
                        $choice['sub_choice_price'] = $meal_choice->sub_choice_price;
                        $meal['sub_choices'][] = $choice;
                        $sub_choices_price += $meal_choice->sub_choice_price;
                        $meal_total_price += $meal_choice->sub_choice_price;
                    }
                }
                $meal['sub_choices_price'] = $sub_choices_price;
                $meal['primary_price'] = $meal_total_price + $sub_choices_price;
                $meal['total_price'] = $meal_total_price * $order_meal->quantity;
                $order_primary_price += $meal['total_price'];
                $cart['items'][] = $meal;
            }
            $cart['info'] = array(
                'order_id' => $order->id,
                'resturant_id' => $order->resturant_id,
                'resturant_slug' => $check_branch_delivery->slug,
                'resturant_branch_id' => $order->resturant_branch_id,
                'service_charge' => $order->service_charge,
                'delivery_cost' => $order->delivery_cost,
                'vat' => $order->vat,
            );
            $cart['price_list']['primary_price'] = $order_primary_price;
            $cart['price_list']['vat_cost'] = (($order_primary_price * $order->vat) / 100);
            $cart['price_list']['service_charge'] = (($order_primary_price * $order->service_charge) / 100);
            $cart['price_list']['delivery_cost'] = $new_delivery_cost;
            $cart['price_list']['total_price'] = $order_primary_price + $cart['price_list']['delivery_cost'] + $cart['price_list']['vat_cost'] + $cart['price_list']['service_charge'];


            $cart['info'] = array(
                'resturant_id' => $order->resturant_id,
                'resturant_slug' => $check_branch_delivery->slug,
                'resturant_branch_id' => $order->resturant_branch_id,
                'service_charge' => $order->service_charge,
                'delivery_cost' => $new_delivery_cost,
                'vat' => $order->vat
            );
            return redirect()->route('showcart')->withCookie(cookie('cart', serialize($cart)));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return _json('error', _lang('app.error_is_occured'));
            }
            return redirect()->back()->with(['errorMessage' => $e->getMessage().$e->getLine()]);
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
