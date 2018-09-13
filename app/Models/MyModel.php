<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\AUTHORIZATION;
use App\Models\User;
use Request;
use DateTime;

class MyModel extends Model {

    protected $lang_code;
    protected static $status_text = [
        0 => 'pending',
        1 => 'in_progress',
        2 => 'on_the_way',
        3 => 'deliverd',
        4 => 'rejected',
        5 => 'order_under_modification',
        6 => 'cancelled',
        7 => 'finish_modification'
    ];

    public function __construct(array $attributes = array()) {
        parent::__construct($attributes);
    }

    protected static function auth_user() {
        $token = Request::header('authorization');
        $token = Authorization::validateToken($token);
        $user = null;
        if ($token) {
            $user = User::find($token->id);
        }

        return $user;
    }

    protected static function getLangCode() {
        $lang_code = app()->getLocale();

        return $lang_code;
    }

    protected static function getCurrencySign() {
        $lang_code = app()->getLocale();
        if ($lang_code == 'ar') {
            $currency_sign = 'جنيه';
        } else {
            $currency_sign = 'EGP';
        }
        return $currency_sign;
    }

    protected static function transformCollection2($items) {

        $transformers = array();

        if (count($items)) {
            foreach ($items as $item) {
                $transformers[] = self::transform($item);
            }
        }

        return $transformers;
    }

    protected static function transformCollection($items, $type = null) {

        $transformers = array();

        if ($type == null) {
            $transform = 'transform';
        } else {
            $transform = 'transform' . $type;
        }
        if (count($items)) {
            foreach ($items as $item) {

                $transformers[] = self::$transform($item);
            }
        }

        return $transformers;
    }

    protected static function handleKeywordWhere($columns, $keyword) {
        $search_exploded = explode(" ", $keyword);
        $i = 0;
        $construct = " ";
        foreach ($columns as $col) {
            //pri($col);
            $x = 0;
            $i++;
            if ($i != 1) {
                $construct .= " OR ";
            }
            foreach ($search_exploded as $search_each) {
                $x++;
                if (count($search_exploded) > 1) {
                    if ($x == 1) {
                        $construct .= "($col LIKE '%$search_each%' ";
                    } else {
                        $construct .= "AND $col LIKE '%$search_each%' ";
                        if ($x == count($search_exploded)) {
                            $construct .= ")";
                        }
                    }
                } else {
                    $construct .= "$col LIKE '%$search_each%' ";
                }
            }
        }
        return $construct;
    }

    protected static function rmv_prefix($old_image) {
        return substr($old_image, strpos($old_image, '_') + 1);
    }

    protected static function checkIsOpen($working_days) {
        $day = date("D");
        if (isset($working_days->{$day})) {

            $currentTime = new DateTime();
            $startTime = new DateTime($working_days->{$day}->from);
            $endTime = new DateTime($working_days->{$day}->to);

            if ($startTime > $endTime) {
                $endTime->modify('+1 day');
            }
            if ($currentTime >= $startTime && $currentTime <= $endTime || $currentTime->modify('+1 day') >= $startTime && $currentTime <= $endTime) {
                return true;
            }
        }
        return false;
    }

}
