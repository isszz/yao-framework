<?php

namespace Yao\Facade;

/**
 * @method static get($key)
 * @method static set($key, $value)
 * @method static \Redis handle()
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
