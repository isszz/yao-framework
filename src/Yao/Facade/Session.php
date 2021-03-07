<?php
declare(strict_types=1);

namespace Yao\Facade;

/**
 * @method static get(string $name)
 * @method static set(string $name, $value)
 * @method static has(string $name)
 * @method static flash(string $name, $value)
 * Class Session
 * @package Yao\Facade
 */
class Session extends Facade
{

    protected static function getFacadeClass()
    {
        return 'session';
    }

}