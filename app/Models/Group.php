<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends MyModel
{
    use SoftDeletes;
    protected $table = "groups";
    protected $dates = ['deleted_at'];
    
    public function admin() {
        return $this->hasMany('App\Models\Admin');
    }
}
