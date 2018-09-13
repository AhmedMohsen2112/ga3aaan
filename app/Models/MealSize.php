<?php

namespace App\Models;

class MealSize extends MyModel {

    protected $table = "meal_sizes";

    public function choices() {
        return $this->belongsToMany(Choice::class, 'meal_choices', 'meal_size_id', 'choice_id')
                        ->select('meal_choices.id as meal_choice_id', 'meal_choices.choice_id', "meal_choices.min", "meal_choices.max");
    }

}
