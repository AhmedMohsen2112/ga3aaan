<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Meal;
use App\Models\MenuSection;
use App\Models\Resturant;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderMeal;
use App\Models\OrderMealTopping;
use App\Models\OrderMealChoice;
use App\Models\Rate;
use App\Models\PaymentMethod;
use App\Models\ResturantBranch;
use App\Models\ResturantBranchDeliveryPlace;
use Validator;
use DB;
use App\Events\updateOrderStatus;

class OrdersController extends ApiController {

    private $rate_rules = array(
        'order_id' => 'required',
        'rate' => 'required',
    );
    private $order_rules = array(
        'order' => 'required',
    );

    public function __construct() {
        parent::__construct();
    }

    public function index(Request $request) {
        
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), $this->order_rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return _api_json('', ['errors' => $errors], 422);
        }
        DB::beginTransaction();
        try {
            $order = json_decode($request->order);

            $user = $this->auth_user();
            $resturnat = Resturant::find($order->order_info->resturant_id);
            //dd($resturnat);
            $coupon = "";
            //checking coupon
            if ($order->order_info->coupon) {

                $coupon = Coupon::where('coupon', $order->order_info->coupon)
                        ->where(function ($query) use($resturnat, $order) {
                            $query->where(function ($query2) use($resturnat, $order) {
                                $query2->where('resturant_id', $resturnat->id);
                                $query2->where('resturant_branch_id', $order->order_info->resturant_branch_id);
                            });
                            $query->orWhere('resturant_id', null);
                        })
                        ->where('available_until', '>=', date('Y-m-d'))
                        ->first();
            }

            if ($request->order_id) {

                $newOrder = Order::find($request->order_id);
                if (!$newOrder) {
                    $message = _lang('app.not_found');
                    return _api_json('', ['message' => $message], 404);
                } else {
                    $newOrder->user_address_id = $order->order_info->user_address_id;
                    $newOrder->payment_method_id = $order->order_info->payment_method_id;

                    $newOrder->primary_price = $order->order_info->primary_price;
                    $newOrder->service_charge = $order->order_info->service_charge;
                    $newOrder->vat = $order->order_info->vat;
                    $newOrder->delivery_cost = $order->order_info->delivery_cost;
                    $newOrder->total_cost = $order->order_info->total_cost;

                    $newOrder->phone = $order->order_info->phone;

                    if ($order->order_info->coupon) {
                        $newOrder->coupon = $coupon->coupon;
                        $newOrder->coupon_discount = $coupon->discount;
                    }
                    $newOrder->net_cost = $order->order_info->net_cost;
                    $newOrder->status = 7;
                    $newOrder->date = date('Y-m-d');
                    $newOrder->save();
                }

                foreach ($newOrder->order_meals as $item) {
                    $item->forceDelete();
                }
            } else {

                //creating new order
                $newOrder = $this->createOrder($user, $resturnat, $order, $coupon);
            }


            //add order meals
            foreach ($order->order_detailes as $meal) {
                $order_meal = $this->createOrderMeal($newOrder, $meal);
                if (count($meal->sub_choices) > 0) {
                    foreach ($meal->sub_choices as $sub_choice) {
                        $this->createOrderChoice($order_meal->id, $sub_choice);
                    }
                }
            }
//
//            if ($request->order_id) {
//
//                $newOrder->status = 1;
//                $newOrder->save();
//            }

            DB::commit();

            if ($request->order_id) {
                $message = "Order #" . $newOrder->id . " modified successfully";
                event(new updateOrderStatus($resturnat->id, $message));
            } else {
                //send notification
                $message = "New order has been received #" . $newOrder->id;
                event(new updateOrderStatus($resturnat->id, $message));
            }

            $message = _lang('app.your_request_has_been_sent_successfully');
            return _api_json('', ['message' => $message]);
        } catch (\Exception $e) {
            DB::rollback();
            $message = _lang('app.error_is_occured');
            return _api_json('', ['message' => $e->getMessage() . $e->getLine()], 422);
        }
    }

    public function show($id) {
        try {
            $user = $this->auth_user();
            $order = Order::withTrashed()->join('resturantes', 'orders.resturant_id', '=', 'resturantes.id')
                    ->join('resturant_branches', 'resturant_branches.id', '=', 'orders.resturant_branch_id')
                    ->join('cities', 'resturant_branches.region_id', '=', 'cities.id')
                    ->select('orders.*', 'resturantes.title_' . $this->lang_code . ' as resturant', 'resturantes.image', 'cities.title_' . $this->lang_code . ' as region')
                    ->where('orders.id', $id)
                    ->where('orders.user_id', $user->id)
                    ->first();
            if (!$order) {
                $message = _lang('app.not_found');
                return _api_json(new \stdClass(), ['message' => $message], 404);
            }

            return _api_json(Order::transform($order));
        } catch (\Exception $e) {

            $message = _lang('app.error_is_occured');
            return _api_json(new \stdClass(), ['message' => $message], 422);
        }
    }

    public function destroy($id) {
        try {

            $order = Order::find($id);
            if (!$order) {
                $message = _lang('app.not_found');
                return _api_json('', ['message' => $message], 404);
            }
            $order_id = $id;
            $resturant_id = $order->resturant_id;

            $current_date = date('Y-m-d H:i:s');
            $acceptance_date = $order->acceptance_date;

            $to_time = strtotime($current_date);
            $from_time = strtotime($acceptance_date);

            $minutes_diff = round(abs($to_time - $from_time) / 60);
            if ($minutes_diff > 5) {
                $message = _lang('app.this_order_cannot_be_canceled');
                return _api_json('', ['message' => $message], 422);
            }
            $order->status = 6;
            $order->save();


            $message = "order #" . $order_id . " cancelled from the client";
            event(new updateOrderStatus($resturant_id, $message));

            $message = _lang('app.canceled_successfully');

            return _api_json('', ['message' => $message]);
        } catch (\Exception $e) {

            $message = _lang('app.error_is_occured');
            return _api_json('', ['message' => $message], 422);
        }
    }

    public function orderUnderModification($id) {
        try {
            $order = Order::find($id);
            if (!$order) {
                $message = _lang('app.not_found');
                return _api_json('', ['message' => $message], 404);
            }

            $current_date = date('Y-m-d H:i:s');
            $acceptance_date = $order->acceptance_date;

            $to_time = strtotime($current_date);
            $from_time = strtotime($acceptance_date);

            $minutes_diff = round(abs($to_time - $from_time) / 60);

            if ($minutes_diff > 3) {
                $message = _lang('app.this_order_cannot_be_edited');
                return _api_json(new \stdClass(), ['message' => $message], 422);
            }

            $order->status = 5;
            $order->save();

            //send notification
            $message = "Order #" . $order->id . " under client modification";
            event(new updateOrderStatus($order->resturant_id, $message));

            return _api_json('');
        } catch (\Exception $e) {
            $message = _lang('app.error_is_occured');
            return _api_json('', ['message' => $message], 422);
        }
    }

    public function cart($id) {
        try {



            $order = Order::find($id);
            if (!$order) {
                $message = _lang('app.not_found');
                return _api_json('', ['message' => $message], 404);
            }

            $current_date = date('Y-m-d H:i:s');
            $acceptance_date = $order->acceptance_date;

            $to_time = strtotime($current_date);
            $from_time = strtotime($acceptance_date);

            $minutes_diff = round(abs($to_time - $from_time) / 60);
            if ($minutes_diff > 3) {
                $message = _lang('app.this_order_cannot_be_edited');
                return _api_json(new \stdClass(), ['message' => $message], 422);
            }

            $order_json = new \stdClass();

            $meals = array();
            $order_info = $this->jsonCreatOrderInfo($order);


            $order_meals = $this->getOrderMeals($id);

            foreach ($order_meals as $order_meal) {

                $meal = $this->jsonCreatMeal($order_meal);

                $choices = $this->getOrderMealChoices($order_meal->id);
                $meal->sub_choices = [];
                if ($choices->count() > 0) {
                    foreach ($choices as $choice) {
                        $meal->sub_choices[] = $this->jsonCreatChoice($choice);
                    }
                }
                array_push($meals, $meal);
            }
            $order_json->order_detailes = $meals;
            $order_json->order_info = $order_info;

            $resturant_payment_methods = PaymentMethod::transformCollection($order->resturant->payment_methods);
            $resturant_branch = ResturantBranch::where('id', $order->resturant_branch_id)->first();

            $city = $resturant_branch->city_id;
            $region = $resturant_branch->region_id;

            return _api_json($order_json, ['payment_methods' => $resturant_payment_methods, 'city' => $city, 'region' => $region]);
        } catch (\Exception $e) {

            $message = _lang('app.error_is_occured');
            return _api_json('', ['message' => $message], 422);
        }
    }

    public function resendOrder(Request $request) {

        try {
            $user = $this->auth_user();
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
                $check_branch_delivery = ResturantBranchDeliveryPlace::where('resturant_branch_id', $order->resturant_branch_id)
                        ->where('region_id', $request->region)
                        ->first();
                if ($check_branch_delivery) {
                    $new_delivery_cost = $check_branch_delivery->delivery_cost;
                } else {
                    return _api_json(new \stdClass(), ['message' => _lang('app.sorry_this_resturant_branch_doesnot_deliver_to_your_address')], 422);
                }
            }

            if ($order->branch_status == false || $order->resturant_status == false || $order->available == false || Resturant::checkIsOpen(json_decode($order->working_hours)) == false) {
                return _api_json(new \stdClass(), ['message' => _lang('app.sorry_this_resturant_is_not_available_now')], 422);
            }
            $new_order_meals = [];
            $order_primary_price = 0;
            $order_meals = Order::getOrderMeals($order->id);
            foreach ($order_meals as $order_meal) {
                $meal_total_price = 0;
                $sub_choices_price = 0;
                $discount = 0;
                if ($order->offer_id) {
                    $discount = $this->getDiscount($order_meal->menu_section_id, $order->offer_id, $order->type, $order->discount, $order->menu_section_ids);
                }

                $meal = new \stdClass();
                $meal->id = $order_meal->meal_id;
                $meal->size_id = $order_meal->meal_size_id;
                $meal->quantity = $order_meal->quantity;

                $meal->price = $order_meal->size_price ? $order_meal->size_price : $order_meal->meal_price;

                $meal->price = $meal->price - (( $meal->price * $discount) / 100);

                //dd($meal->price);
                $meal->comment = $order_meal->comment;
                $meal->meal_title = $order_meal->meal_title;
                $meal->size_title = $order_meal->size_title;
                $meal_total_price += $meal->price;
                $meal_choices = Order::getOrderMealChoices($order_meal->id);
                $meal->sub_choices = [];
                if ($meal_choices->count() > 0) {
                    foreach ($meal_choices as $meal_choice) {
                        $choice = new \stdClass();
                        $choice->id = $meal_choice->id;
                        $choice->title = $meal_choice->title;
                        $choice->sub_choice_price = $meal_choice->sub_choice_price;

                        $meal->sub_choices[] = $choice;
                        $sub_choices_price += $choice->sub_choice_price;
                        $meal_total_price += $choice->sub_choice_price;
                    }
                }
                $meal->sub_choices_price = $sub_choices_price;
                $meal->total_price = $meal_total_price * $meal->quantity;
                $order_primary_price += $meal->total_price;
                array_push($new_order_meals, $meal);
            }

            $order_total_price = $order_primary_price + $new_delivery_cost + (($order_primary_price * $order->vat) / 100) + (($order_primary_price * $order->service_charge) / 100);

            $order_json = new \stdClass();

            $meals = array();


            $order_info = new \stdClass();
            $order_info->resturant_id = $order->resturant_id;
            $order_info->resturant_branch_id = $order->resturant_branch_id;
            $order_info->user_address_id = $order->user_address_id;
            $order_info->payment_method_id = $order->payment_method_id;
            $order_info->primary_price = $order_primary_price;
            $order_info->service_charge = $order->service_charge;
            $order_info->vat = $order->vat;
            $order_info->delivery_cost = $new_delivery_cost;
            $order_info->total_cost = $order_total_price;
            $order_info->phone = $order->phone;
            $order_info->net_cost = $order_total_price;
            $order_info->coupon = "";
            $order_json->order_detailes = $new_order_meals;
            $order_json->order_info = $order_info;
            $resturant_payment_methods = PaymentMethod::transformCollection($order->resturant->payment_methods);

            return _api_json($order_json, ['payment_methods' => $resturant_payment_methods]);
        } catch (\Exception $e) {

            $message = _lang('app.error_is_occured');
            return _api_json(new \stdClass(), ['message' => $e->getMessage() . $e->getLine()], 422);
        }
    }

    public function resendOrder2(Request $request) {

        try {
            $user = $this->auth_user();
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
                $check_branch_delivery = ResturantBranchDeliveryPlace::where('resturant_branch_id', $order->resturant_branch_id)
                        ->where('region_id', $request->region)
                        ->first();
                if ($check_branch_delivery) {
                    $new_delivery_cost = $check_branch_delivery->delivery_cost;
                } else {
                    return _api_json(new \stdClass(), ['message' => _lang('app.sorry_this_resturant_branch_doesnot_deliver_to_your_address')], 422);
                }
            }

            if (
                    $order->branch_status == false ||
                    $order->resturant_status == false ||
                    $order->available == false ||
                    Resturant::checkIsOpen(json_decode($order->working_hours)) == false
            ) {
                return _api_json(new \stdClass(), ['message' => _lang('app.sorry_this_resturant_is_not_available_now')], 422);
            }

            $order_meals = OrderMeal::withTrashed()->where('order_id', $request->order_id)
                    ->join('meals', 'order_meals.meal_id', '=', 'meals.id')
                    ->join('menu_sections', 'meals.menu_section_id', '=', 'menu_sections.id')
                    ->leftJoin('meal_sizes', 'order_meals.meal_size_id', '=', 'meal_sizes.id')
                    ->leftJoin('sizes', 'meal_sizes.size_id', '=', 'sizes.id')
                    ->select('order_meals.id', 'order_meals.comment', 'order_meals.meal_id', 'order_meals.meal_size_id', 'order_meals.quantity', 'meals.price as meal_price', 'meal_sizes.price as size_price', 'menu_sections.id as menu_section_id', 'meals.title_' . $this->lang_code . ' as meal_title', 'sizes.title_' . $this->lang_code . ' as size_title')
                    ->get();


            foreach ($order_meals as $order_meal) {

                $order_meal_toppings = OrderMeal::withTrashed()->
                                leftJoin('order_meal_toppings', 'order_meal_toppings.order_meal_id', '=', 'order_meals.id')
                                ->join('meal_toppings', 'order_meal_toppings.meal_topping_id', '=', 'meal_toppings.id')
                                ->join('menu_section_toppings', 'meal_toppings.menu_section_topping_id', '=', 'menu_section_toppings.id')
                                ->where('order_meal_toppings.order_meal_id', $order_meal->id)
                                ->select('order_meal_toppings.meal_topping_id', 'order_meal_toppings.quantity', 'menu_section_toppings.price')
                                ->get()->toArray();

                if (!empty($order_meal_toppings)) {
                    foreach ($order_meal_toppings as $key => $value) {
                        $order_meal->toppings_price += $value['price'] * $value['quantity'];
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
            //$new_delivery_cost = $order->delivery_cost;
            $new_service_charge = $order->service_charge;


            $new_total_cost = $new_primary_price + $new_toppings_price + $new_delivery_cost + (($new_primary_price * $new_vat) / 100) + (($new_primary_price * $new_service_charge) / 100);


            $order_json = new \stdClass();

            $meals = array();


            $order_info = new \stdClass();

            $order_info->resturant_id = $order->resturant_id;
            $order_info->resturant_branch_id = $order->resturant_branch_id;
            $order_info->user_address_id = $order->user_address_id;
            $order_info->payment_method_id = $order->payment_method_id;
            $order_info->primary_price = $new_primary_price;
            $order_info->service_charge = $new_service_charge;
            $order_info->vat = $new_vat;
            $order_info->delivery_cost = $new_delivery_cost;
            $order_info->total_cost = $new_total_cost;
            $order_info->phone = $order->phone;
            $order_info->net_cost = $new_total_cost;
            $order_info->toppings_price = $new_toppings_price;
            $order_info->coupon = "";

            foreach ($order_meals as $order_meal) {

                $meal = $this->jsonCreatMeal($order_meal);
                if ($order_meal->topppings) {
                    $toppings = array();
                    foreach ($order_meal->topppings as $meal_topping) {

                        $topping = new \stdClass();
                        $topping->id = $meal_topping['meal_topping_id'];
                        $topping->quantity = $meal_topping['quantity'];
                        $topping->price = $meal_topping['price'];
                        $topping->total_price = $meal_topping['quantity'] * $meal_topping['price'];
                        array_push($toppings, $topping);
                    }
                    $meal->toppings = $toppings;
                } else {
                    $meal->toppings = array();
                }
                array_push($meals, $meal);
            }
            $order_json->order_detailes = $meals;
            $order_json->order_info = $order_info;

            $resturant_payment_methods = PaymentMethod::transformCollection($order->resturant->payment_methods);

            return _api_json($order_json, ['payment_methods' => $resturant_payment_methods]);
        } catch (\Exception $e) {

            $message = _lang('app.error_is_occured');
            return _api_json(new \stdClass(), ['message' => $message], 422);
        }
    }

    public function rate(Request $request) {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), $this->rate_rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return _api_json('', ['errors' => $errors], 422);
            }
            $order = Order::find($request->order_id);
            if (!$order) {
                $message = _lang('app.not_found');
                return _api_json('', ['message' => $message], 404);
            }
            $user = $this->auth_user();
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

            $resturant = ResturantBranch::find($order->resturant_branch_id);
            $resturant->rate = $resturant_branch_rate->rate;
            $resturant->save();


            DB::commit();
            $message = _lang('app.rated_successfully');
            return _api_json('', ['message' => $message]);
        } catch (\Exception $e) {

            DB::rollback();
            $message = _lang('app.error_is_occured');
            return _api_json('', ['message' => $message], 422);
        }
    }

    private function jsonCreatOrderInfo($order) {

        $order_info = new \stdClass();
        $order_info->resturant_id = $order->resturant_id;
        $order_info->resturant_branch_id = $order->resturant_branch_id;
        $order_info->user_address_id = $order->user_address_id;
        $order_info->payment_method_id = $order->payment_method_id;
        $order_info->primary_price = $order->primary_price;
        $order_info->service_charge = $order->service_charge;
        $order_info->vat = $order->vat;
        $order_info->delivery_cost = $order->delivery_cost;
        $order_info->total_cost = $order->total_cost;
        $order_info->net_cost = $order->net_cost;
        $order_info->phone = $order->phone;
        $order_info->coupon = $order->coupon;


        return $order_info;
    }

    private function jsonCreatMeal($order_meal) {
        $meal = new \stdClass();
        $meal->id = $order_meal->meal_id;
        $meal->size_id = $order_meal->meal_size_id;
        $meal->quantity = $order_meal->quantity;
        $meal->price = $order_meal->cost_of_meal;
        $meal->total_price = $order_meal->cost_of_quantity;
        $meal->sub_choices_price = $order_meal->sub_choices_price;
        $meal->comment = $order_meal->comment;
        $meal->meal_title = $order_meal->meal_title;
        $meal->size_title = $order_meal->size_title;
        return $meal;
    }

    private function jsonCreatChoice($meal_choice) {

        $choice = new \stdClass();
        $choice->id = $meal_choice->id;
        $choice->title = $meal_choice->title;
        $choice->price = $meal_choice->price;

        return $choice;
    }

    private function createOrder($user, $resturnat, $order, $coupon) {
        $newOrder = new Order;
        $newOrder->user_id = $user->id;
        $newOrder->resturant_id = $resturnat->id;
        $newOrder->resturant_branch_id = $order->order_info->resturant_branch_id;
        $newOrder->user_address_id = $order->order_info->user_address_id;
        $newOrder->payment_method_id = $order->order_info->payment_method_id;

        $newOrder->primary_price = $order->order_info->primary_price;
        $newOrder->service_charge = $order->order_info->service_charge;
        $newOrder->vat = $order->order_info->vat;
        $newOrder->delivery_cost = $order->order_info->delivery_cost;
        $newOrder->total_cost = $order->order_info->total_cost;

        $newOrder->phone = $order->order_info->phone;

        if ($order->order_info->coupon) {
            $newOrder->coupon = $coupon->coupon;
            $newOrder->coupon_discount = $coupon->discount;
        }
        $newOrder->net_cost = $order->order_info->net_cost;
        $newOrder->status = 0;
        $newOrder->commission = $resturnat->commission;

        $newOrder->date = date('Y-m-d H:i:s');
        $newOrder->save();

        return $newOrder;
    }

    private function editOrder($user, $resturnat, $order) {
        $newOrder = new Order;
        $newOrder->user_id = $user->id;
        $newOrder->resturant_id = $resturnat->id;
        $newOrder->resturant_branch_id = $order->order_info->resturant_branch_id;
        $newOrder->user_address_id = $order->order_info->user_address_id;
        $newOrder->payment_method_id = $order->order_info->payment_method_id;

        $newOrder->primary_price = $order->order_info->primary_price;
        $newOrder->service_charge = $order->order_info->service_charge;
        $newOrder->vat = $order->order_info->vat;
        $newOrder->delivery_cost = $order->order_info->delivery_cost;
        $newOrder->total_cost = $order->order_info->total_cost;

        $newOrder->phone = $order->order_info->phone;

        if ($order->order_info->coupon) {
            $newOrder->coupon = $coupon->coupon;
            $newOrder->coupon_discount = $coupon->discount;
        }
        $newOrder->net_cost = $order->order_info->net_cost;
        $newOrder->status = 0;
        $newOrder->commission = $resturnat->commission;
        $newOrder->toppings_price = $order->order_info->toppings_price;
        $newOrder->date = date('Y-m-d H:i:s');
        $newOrder->save();

        return $newOrder;
    }

    private function createOrderMeal($newOrder, $meal) {


        $order_meal = new OrderMeal;
        $order_meal->order_id = $newOrder->id;
        $order_meal->meal_id = $meal->id;
        $order_meal->meal_size_id = $meal->size_id == 0 ? null : $meal->size_id;
        $order_meal->quantity = $meal->quantity;
        $order_meal->cost_of_meal = $meal->price;
        $order_meal->cost_of_quantity = $meal->total_price;
        $order_meal->sub_choices_price = $meal->sub_choices_price;
        $order_meal->comment = $meal->comment;

        $order_meal->save();

        return $order_meal;
    }

    private function createOrderChoice($order_meal_id, $sub_choice) {

        $OrderMealChoice = new OrderMealChoice;
        $OrderMealChoice->order_meal_id = $order_meal_id;
        $OrderMealChoice->sub_choice_id = $sub_choice->id;
        $OrderMealChoice->price = $sub_choice->price;

        $OrderMealChoice->save();
    }

    private function createOrderTopping($order_meal, $topping) {

        $order_meal_topping = new OrderMealTopping;
        $order_meal_topping->order_meal_id = $order_meal->id;
        $order_meal_topping->meal_topping_id = $topping->id;
        $order_meal_topping->quantity = $topping->quantity;
        $order_meal_topping->cost_of_topping = $topping->price;
        $order_meal_topping->cost_of_quantity = $topping->total_price;

        $order_meal_topping->save();
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

    private function getOrderMeals($order_id) {
        $order_meals = OrderMeal::where('order_id', $order_id)
                ->join('meals', 'order_meals.meal_id', '=', 'meals.id')
                ->join('menu_sections', 'meals.menu_section_id', '=', 'menu_sections.id')
                ->leftJoin('meal_sizes', 'order_meals.meal_size_id', '=', 'meal_sizes.id')
                ->leftJoin('sizes', 'meal_sizes.size_id', '=', 'sizes.id')
                ->select(['order_meals.id', 'order_meals.meal_id_in_cart', 'order_meals.comment', 'order_meals.meal_id', 'order_meals.meal_size_id', 'order_meals.quantity',
                    'order_meals.cost_of_meal', 'order_meals.cost_of_quantity', "order_meals.sub_choices_price",
                    'menu_sections.id as menu_section_id', 'meals.title_' . $this->lang_code . ' as meal_title', 'sizes.title_' . $this->lang_code . ' as size_title'])
                ->get();
        return $order_meals;
    }

    private function getOrderMealChoices($order_meal_id) {
        $choices = OrderMeal::join('order_meal_choices', 'order_meals.id', '=', 'order_meal_choices.order_meal_id')
                ->join('sub_choices', 'sub_choices.id', '=', 'order_meal_choices.sub_choice_id')
                ->where('order_meals.id', $order_meal_id)
                ->select([
                    "sub_choices.id", "sub_choices.title_$this->lang_code as title", "order_meal_choices.price", "sub_choices.price as sub_choice_price"
                ])
                ->get();


        return $choices;
    }

}
