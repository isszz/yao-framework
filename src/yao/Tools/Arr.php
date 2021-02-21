<?php


namespace Yao\Tools;


class Arr
{
    public static function isAssoc(array $array)
    {
        return !self::isNumber($array);
    }

    public static function isNumber(array $array)
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    public static function getAssoc(array $array)
    {
        $return = [];
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $return[$value] = null;
            } else {
                $return[$key] = $value;
            }
        }
        return $return;
    }
}