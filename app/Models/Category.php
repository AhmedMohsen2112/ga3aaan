<?php

namespace App\Models;

class Category extends MyModel {

    public static function transform(Category $item) {
        $titleSlug = "title_" . static::getLangCode();
        $transformer = new Category();
        $transformer->id = $item->id;
        $transformer->title = $item->$titleSlug;

        return $transformer;
    }

    public static function transformResturantesPage($item) {

        return $item;
    }

}
