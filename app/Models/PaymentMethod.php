<?php

namespace App\Models;

class PaymentMethod extends MyModel
{

	public static function transform($item)
	{
		$titleSlug = 'title_'.static::getLangCode();

		$transformer = new \stdClass();
		$transformer->id = $item->id;
		$transformer->title = $item->$titleSlug;

		return $transformer;
	}
   
}
