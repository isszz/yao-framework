<?php

namespace Yao\Facade;

/**
 * @method get($key)
 * @method set($key, $value)
 * Class Cache
 * @package Yao\Facade
 */
class Cache extends Facade
{

    protected static function getFacadeClass()
    {
        return 'cache';
    }
}
