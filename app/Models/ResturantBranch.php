<?php

namespace App\Models;

class ResturantBranch extends MyModel
{
	protected $table = "resturant_branches";

	public function delivery_places()
	{
		return $this->belongsToMany(City::class,'branch_delivery_places','resturant_branch_id','region_id');
	}

	public function rates() {
        return $this->hasMany(Rate::class,'resturant_branch_id');
    }


	 protected static function boot() {
        parent::boot();

        static::deleting(function($resturant_branch) {
            $resturant_branch->delivery_places()->detach();
        });
    }
}
