<?php

namespace App\Models;

class Offer extends MyModel {

    public static $sizes = array(
        's' => array('width' => 120, 'height' => 120),
        'm' => array('width' => 380, 'height' => 280),
    );

    public function resturant() {
        return $this->belongsTo(Resturant::class);
    }

    public static function transform($item) {
        $transformer = new \stdClass();
        $titleSlug = "title_" . static::getLangCode();
        $message = _lang('app.offer') . ' ';
        if ($item->discount) {
            $message .= $item->discount . ' % ';
        }
        $message .= _lang('app.from') . ' ' . $item->resturant->{$titleSlug};

        $transformer->offer = $message;

        if ($item->type == 2) {
            $menu_sections = static::menuSections($item->menu_section_ids);
            $transformer->detailes = _lang('app.this_offer_is_not_valid_on') . ' ' .  _lang('app.categories'). ' ' . $menu_sections;
        } elseif ($item->type == 3) {

            $menu_sections = static::menuSections($item->menu_section_ids);
            $transformer->detailes = _lang('app.this_offer_is_valid_on') . ' ' .  _lang('app.categories'). ' ' . $menu_sections;
        } else {
            $transformer->detailes = "";
        }
        $transformer->valid_until = $item->available_until;
        $transformer->resturant_id = $item->resturant->id;
        
        //edited
        $transformer->resturant_branch_id = $item->resturant_branch_id ? $item->resturant_branch_id : 0;
        //$transformer->region = $item->region ? $item->region : 0;
        $transformer->resturant_title = $item->resturant_title ? $item->resturant_title : "";
        $transformer->branch_title = $item->branch_title ? $item->branch_title : "";
        //

        $transformer->type = $item->type;
        
        $transformer->image = url('public/uploads/offers') . '/' . $item->image;
        return $transformer;
    }
    public static function transformOffersPage($item) {
        $transformer = new \stdClass();
        $message = _lang('app.offer') . ' ';
        if ($item->discount) {
            $message .= $item->discount . ' % ';
        }
        $message .= _lang('app.from') . ' ' . $item->resturant_title;

        $transformer->offer = $message;

        if ($item->type == 2) {
            $menu_sections = static::menuSections($item->menu_section_ids);
            $transformer->detailes = _lang('app.this_offer_is_not_valid_on') . ' ' . $menu_sections . ' ' . _lang('app.categories');
        } elseif ($item->type == 3) {

            $menu_sections = static::menuSections($item->menu_section_ids);
            $transformer->detailes = _lang('app.this_offer_is_valid_on') . ' ' . $menu_sections . ' ' . _lang('app.categories');
        } else {
            $transformer->detailes = "";
        }
        $transformer->available_until = $item->available_until;
        $transformer->resturant_slug = $item->resturant_slug;
        $transformer->resturant_title = $item->resturant_title;
        $transformer->image = url('public/uploads/offers') . '/' . $item->image;
        return $transformer;
    }
    public static function transformHome($item) {
        $item->image = url('public/uploads/offers/m_' . static::rmv_prefix($item->image));
        return $item;
    }

    private static function menuSections($ids) {

        $menu_section_ids = explode(",", $ids);
        $menu_sections = MenuSection::whereIn('id', $menu_section_ids)->pluck('title_' . static::getLangCode())->toArray();
        $menu_sections = implode(", ", $menu_sections);
        return $menu_sections;
    }

}
