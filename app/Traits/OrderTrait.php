<?php

namespace App\Traits;
use App\Models\Order;
use App\Models\Meal;
use App\Models\OrderMeal;
use DB;

trait OrderTrait {

    public function show($id) {
        $order = $this->getOrder($id);
        if (!$order) {
            return $this->err404();
        }
        $order->canEdit= $this->canEditOrder($order);
        $this->data['order'] = $order;
        $meals = $this->getOrderMeals($order->id);
        if ($meals->count() > 0) {
            foreach ($meals as $meal) {
                $meal->sub_choices = $this->getOrderMealChoices($meal->id);
            }
        }
        //dd($order);
        $this->data['meals'] = $meals;
        return $this->_view('orders_reports/view', 'backend');
    }
    private function getOrder($id) {
        $order = Order::join('resturantes', 'resturantes.id', '=', 'orders.resturant_id')
                ->join('resturant_branches', 'orders.resturant_branch_id', '=', 'resturant_branches.id')
                ->join('cities as city', 'city.id', '=', 'resturant_branches.city_id')
                ->join('cities as region', 'region.id', '=', 'resturant_branches.region_id')
                ->join('payment_methods', 'payment_methods.id', '=', 'orders.payment_method_id')
                ->join('addresses', 'addresses.id', '=', 'orders.user_address_id')
                ->join('users', 'users.id', '=', 'orders.user_id')
                ->where('orders.id', $id)
                ->select([
                    'orders.id', "city.title_$this->lang_code as city_title", "region.title_$this->lang_code as region_title","orders.coupon_discount",
                    "orders.commission","orders.refusing_reason",
                    "orders.status", "payment_methods.title_$this->lang_code as payment_method", "orders.total_cost", "orders.primary_price","orders.net_cost",
                    "orders.toppings_price", "orders.vat", "orders.service_charge", "orders.coupon", "orders.delivery_cost","orders.acceptance_date",
                    "orders.created_at", "addresses.id as address_id", "addresses.city", "addresses.region", "addresses.sub_region",
                    "addresses.street", "addresses.building_number", "addresses.floor_number", "addresses.apartment_number", "addresses.special_sign",
                    "addresses.extra_info", DB::RAW("CONCAT(users.first_name,' ',users.last_name) as client"), 'users.email', "users.mobile",
                    DB::RAW("CONCAT(resturantes.title_$this->lang_code,' ',resturant_branches.title_$this->lang_code) as resturant_title")
                ])
                ->first();
        if ($order) {
            $order->long_address = implode(' , ', array(
                $order->city, $order->region, $order->sub_region, $order->street,
                $order->building_number, $order->floor_number, $order->apartment_number
            ));
        }

        return $order;
    }

    private function getOrderMeals($order_id) {
        $meals = Meal::join('order_meals', 'meals.id', '=', 'order_meals.meal_id')
                ->join('orders', 'orders.id', '=', 'order_meals.order_id')
                ->where('orders.id', $order_id)
                ->select([
                    "order_meals.id", "meals.title_$this->lang_code as title", "order_meals.quantity", "order_meals.cost_of_meal",
                    "order_meals.cost_of_quantity","order_meals.sub_choices_price"
                ])
                ->get();
        return $meals;
    }

    private function getOrderMealChoices($order_meal_id) {
        $choices = OrderMeal::join('order_meal_choices', 'order_meals.id', '=', 'order_meal_choices.order_meal_id')
                ->join('sub_choices', 'sub_choices.id', '=', 'order_meal_choices.sub_choice_id')
                ->where('order_meals.id', $order_meal_id)
                ->select([
                    "sub_choices.title_$this->lang_code as title", "order_meal_choices.price",
                ])
                ->get();


        return $choices;
    }
    private function canEditOrder($order) {

        $current_date = date('Y-m-d H:i:s');
        $acceptance_date = $order->acceptance_date;

        $to_time = strtotime($current_date);
        $from_time = strtotime($acceptance_date);

        $minutes_diff = round(abs($to_time - $from_time) / 60);

        if ($minutes_diff < 3) {
            return true;
        }
        return false;
    }

}
