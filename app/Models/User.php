<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Order;
use DB;

class User extends Authenticatable {

    use Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'mobile',
        'sms_notify',
        'email_notify',
        'password',
    ];

    public function favourites()
    {
      return $this->belongsToMany(Meal::class,'favourites','user_id','meal_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class,'user_id');
    }
 

    protected static function transformCollection($items, $type =null) {

        $transformers = array();

       if($type==null){
           $transform = 'transform';
       }else{
           $transform = 'transform'.$type;
       }
        if (count($items)) {
            foreach ($items as $item) {

                 $transformers[] = self::$transform($item);
            }
        }

        return $transformers;
    }
    
    public static function transform(User $item) {
       
        $transformer = new User();
        $transformer->first_name = $item->first_name;
        $transformer->last_name = $item->last_name;
        $transformer->email = $item->email;
        $transformer->mobile = "'$item->mobile'";
        if ($item->user_image) {
           $transformer->image = url('public/uploads/users').'/'.$item->user_image;
        } else {
             $transformer->image = url('public/uploads/users/default.png');
        }
        $transformer->sms_notify = $item->sms_notify;
        $transformer->email_notify = $item->email_notify;

        return $transformer;
    }




    public static function transformFavourites($item) {
       
        $transformer = new \stdClass();
        $transformer->meal_id = $item->meal_id;
        $transformer->resturant_id = $item->resturant_id;
        $transformer->meal = $item->meal;
        $transformer->meal_image = url('public/uploads/meals').'/'.$item->image;
        $transformer->resturant = $item->resturant;
        $transformer->branch = $item->branch;
        $transformer->price = $item->price;
        $transformer->resturant_branch_id = $item->resturant_branch_id;
        return $transformer;
    }


    protected static function boot() {
        parent::boot();

        static::deleting(function($user) {
           foreach ($user->orders as $order) {
               $order->delete();
           }
        });
    }

}
