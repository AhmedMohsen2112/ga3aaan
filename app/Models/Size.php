<?php

namespace App\Models;

class Size extends MyModel
{
	 protected $hidden = array('pivot');
	 
    public static function transform($item)
    {
    	$titleSlug = 'title_'.static::getLangCode();
    	$transformer = new \stdClass();
    	$transformer->id = $item->id;
    	$transformer->title = $item->$titleSlug;
    	return $transformer;
    }
}
