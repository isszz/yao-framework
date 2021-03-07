<?php
declare(strict_types=1);

namespace Yao\Facade;

/**
 * @method static set(string $env, mixed $value)
 * @method static string get(string $key = null, $default = null)
 * Class Env
 * @package Yao\Facade
 */
class Env extends Facade
{

    protected static function getFacadeClass()
    {
        return 'env';
    }

}