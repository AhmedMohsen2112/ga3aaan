<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable {

    use Notifiable;
    use SoftDeletes;

    protected $table = "admins";
    //protected $guard = "admin";
    protected $fillable = [
        'username', 'email', 'password',
    ];
    protected $dates = ['deleted_at'];

    public function group() {
        return $this->belongsTo('App\Models\Group', 'group_id', 'id');
    }

    public function resturant() {
        return $this->hasOne(Resturant::class, 'admin_id', 'id');
    }

}
