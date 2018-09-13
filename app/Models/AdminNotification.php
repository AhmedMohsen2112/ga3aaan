<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AdminNotification extends MyModel {

    use Notifiable;

    protected $table = 'admin_notifications';

}
