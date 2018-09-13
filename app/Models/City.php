<?php

namespace App\Models;

class City extends MyModel
{
   
    public function regions()
    {
        return $this->hasMany(City::class,'parent_id');
    }

    public static function  transform(City $item)
    {
        $transformer = new City();
        $titleSlug = "title_".static::getLangCode();
        $transformer->id = $item->id;
        $transformer->title = $item->$titleSlug;
        if ($item->parent_id == 0) {
             $transformer->regions = City::transformCollection($item->regions);
        }
        return $transformer;
    }

     protected static function boot() {
        parent::boot();

        static::deleting(function($city) {
            foreach ($city->regions as $region) {
                $region->delete();
            }
          ;
        });
    }



    
}
