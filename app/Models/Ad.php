<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends MyModel {

    protected $table = 'ads';
    public static $sizes = array(
        's' => array('width' => 120, 'height' => 120),
        'm' => array('width' => 600, 'height' => 75),
        'l' => array('width' => 350, 'height' => 100),
    );

    public static function transform($item) {
        $obj = new \stdClass();
        $obj->url = $item->url;
        $item->ad_image = str_replace("s_","l_",$item->ad_image);
        $obj->image = url('public/uploads/ads/' . $item->ad_image);

        return $obj;
    }
    public static function transformHome($item) {
        $item->image = url('public/uploads/ads/m_' . static::rmv_prefix($item->image));
        return $item;
    }

}
