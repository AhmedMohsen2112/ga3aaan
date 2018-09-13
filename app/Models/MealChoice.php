<?php

namespace App\Models;

class MealChoice extends MyModel {

    protected $table = "meal_choices";

    public function sub_choices() {
        return $this->belongsToMany(SubChoice::class, 'meal_sub_choices', 'meal_choice_id', 'sub_choice_id')
                        ->select('meal_sub_choices.sub_choice_id');
    }


    protected static function boot() {
        parent::boot();

        static::deleting(function($meal) {
            $meal->sub_choices->delete();
        });
    }

}
