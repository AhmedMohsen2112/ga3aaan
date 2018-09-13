<?php

namespace App\Models;



class MenuSection extends MyModel
{
    protected $table = "menu_sections";

    public function toppings()
    {
      return $this->belongsToMany(Topping::class,'menu_section_toppings','menu_section_id','topping_id')->withPivot('id','price');
    }

     public function resturant()
    {
      return $this->belongsTo(Resturant::class);
    }

    public static function transform($item)
    {
        $titleSlug = "title_".static::getLangCode();
        
        $transform = new MenuSection;
        $transform->id = $item->id;
        $transform->title = $item->{$titleSlug};
        $transform->slug = $item->slug;

        return $transform;
    }


    protected static function boot() {
        parent::boot();

        static::deleting(function($menuSection) {
          //$menuSection->toppings()->detach();
        });
    }



}
