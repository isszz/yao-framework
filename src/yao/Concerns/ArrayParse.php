<?php


namespace Yao\Concerns;


trait ArrayParse
{

    /**
     * 判断数组是不是关联数组
     * @param array $array
     * @return bool
     */
    public function isAssoc(array $array): bool
    {
        return array_keys($array) !== range(0, count($array));
    }
}