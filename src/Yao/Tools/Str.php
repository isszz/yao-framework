<?php


namespace Yao\Tools;


class Str
{
    public static function parse(array $value, string $string, $default = null)
    {
        $field = explode('.', $string);
        foreach ($field as $v) {
            if (isset($value[$v])) {
                $value = $value[$v];
            } else {
                $value = $default;
                break;
            }
        }
        return $value;
    }
}