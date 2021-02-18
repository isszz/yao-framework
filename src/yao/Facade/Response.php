<?php

namespace Yao\Facade;

/**
 * @method static \Yao\Http\Response data($data)
 * @method static get(string $key = null, $default = null)
 * Class Config
 * @package Yao\Facade
 */
class Response extends Facade
{

    protected static function getFacadeClass()
    {
        return 'response';
    }
}
