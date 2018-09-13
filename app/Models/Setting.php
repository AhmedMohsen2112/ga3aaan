<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends MyModel {

    protected $table = 'settings';

    public static function transform($item) {
        $lang_code = static::getLangCode();

        $item->terms_conditions = $item->{'terms_conditions_' . $lang_code};

        $item->usage_conditions = $item->{'usage_conditions_' . $lang_code};

        $item->about_us = $item->{'about_us_' . $lang_code};
        unset(
                $item->id,$item->terms_conditions_ar, $item->terms_conditions_en, $item->usage_conditions_ar, $item->usage_conditions_en,  $item->about_us_ar, $item->about_us_en,$item->created_at,$item->updated_at,$item->phone,$item->email,$item->fax,$item->social_media
        );
        return $item;
    }


}
