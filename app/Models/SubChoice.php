<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubChoice extends MyModel {

    protected $table = 'sub_choices';

    public static function transformForOrder($item) {
        $choice = new \stdClass();
        $choice->id = $item->id;
        $choice->title = $item->title;
        $choice->price = $item->price;

        return $choice;
    }

}
