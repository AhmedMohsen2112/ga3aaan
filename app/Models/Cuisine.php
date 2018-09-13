<?php

namespace App\Models;



class Cuisine extends MyModel
{
    public static function transform($item)
    {
        $titleSlug = "title_".static::getLangCode();
        
        $transform = new Cuisine;
        $transform->id = $item->id;
        $transform->title = $item->{$titleSlug};

        return $transform;
    }

   public static function transformResturant($item)
    {
        $titleSlug = "title_".static::getLangCode();
        
        $transform = new Cuisine;
        $transform->id = $item->id;
        $transform->title = $item->{$titleSlug};

        return $transform;
    }
    //front
   public static function transformResturantesPage($item)
    {
       

        return $item;
    }

 
}
