<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\AdminNotification;
use App\Models\Notification;
use App\Models\Friendship;

class NotificationsController extends ApiController {

    public function __construct() {
        parent::__construct();
    }

    public function index2(Request $request) {

        $user = $this->auth_user();
        $notifications = $user->notifications()
                ->paginate($this->limit);

        //dd($notifications);
        $user_notifications = Notification::transformCollection($notifications);
        $admin_notifications = AdminNotification::all();
        $admin_notifications = $this->filterAdminNotification($admin_notifications);
        $admin_notifications = AdminNotification::transformCollection($admin_notifications);
        $notifications = array_merge($user_notifications, $admin_notifications);
        if (count($notifications) > 0) {
            foreach ($notifications as $key => $row) {
                $creation_time[$key] = $row->created_at;
            }
            array_multisort($creation_time, SORT_DESC, $notifications);
        }
        return _api_json(true, $notifications, 200);
    }

    public function index(Request $request) {
        $this->limit = $this->limit / 2;
        $user = $this->auth_user();
        //dd($user);
        $date =  strtotime("-1 month",time());
        $date = date("Y-m-d H:i:s",$date);
        //dd($date);
        $user_notifications = $user->notifications()->where("created_at",">=",$date)->paginate($this->limit);
        $total = $user_notifications->total();

        $user_notifications = Notification::transformCollection($user_notifications);
        
        return _api_json(true, $user_notifications, ['total' => $total / $this->limit]);
    }

    private function filterAdminNotification($admin_notifications) {
        //dd($admin_notifications);
        $new_admin_notification = array();
        if ($admin_notifications->count() > 0) {
            foreach ($admin_notifications as $one) {
                $data = json_decode($one->data);
                $new_admin_notification[] = $one;
            }
        }
        return $new_admin_notification;
    }

}
