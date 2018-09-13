<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends MyModel {

    use SoftDeletes;
    protected $dates = ['deleted_at'];


    public function orders()
    {
        return $this->hasMany(Order::class,'user_address_id');
    }

    public static function transform($item) {

        $transformer = new Address();
        $transformer->id = $item->id;
        $transformer->city = $item->city;
        $transformer->region = $item->region;
        $transformer->sub_region = $item->sub_region;
        $transformer->street = $item->street;
        $transformer->building_number = $item->building_number;
        $transformer->floor_number = $item->floor_number;
        $transformer->apartment_number = $item->apartment_number;
        $transformer->special_sign = $item->special_sign;
        $transformer->extra_info = $item->extra_info;
        $transformer->long_address = implode(' , ', array(
            $transformer->city, $transformer->region, $transformer->sub_region, $transformer->street,
            $transformer->building_number, $transformer->floor_number, $transformer->apartment_number
        ));

        return $transformer;
    }

    protected static function boot() 
    {
        parent::boot();

        static::deleting(function($address) 
        {
//            foreach ($address->orders as $order) {
//                $order->delete();
//            } 
        });
    }

}
