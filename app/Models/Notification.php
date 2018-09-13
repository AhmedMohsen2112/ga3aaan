<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Setting;

class Notification extends MyModel {

    protected $table = 'notifications';

    public static function transform($item) {
        $obj = new \stdClass();
        $obj->id = $item->data['id'];
        $obj->created_at = date("j F Y   g:i a", strtotime($item->created_at));
        $obj->title = $item->data['title'];
        $obj->message = $item->data['message'];
        $obj->type = $item->data['type'];
        if ($obj->type == 1 && isset($item->data['status_no'])) {
            $Setting = Setting::first();
            $order_status_messages = collect(json_decode($Setting->order_status_messages))->toArray();
            if(isset($order_status_messages[$item->data['status_no']])){
                $obj->hint=$order_status_messages[$item->data['status_no']];
            }
        }
        return $obj;
    }

}
