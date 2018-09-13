<?php

namespace App\Models;



class Rate extends MyModel
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
   
    public static function  transform($item)
    {
       
        $transformer = new \stdClass();
       
        $transformer->user = $item->user->first_name . ' ' .  $item->user->last_name;
        $transformer->rate = $item->rate;
        if ($item->opinion) {
            $transformer->opinion = $item->opinion;
        }
        else{
            $transformer->opinion ="";

        }
        return $transformer;

    }



    
}
