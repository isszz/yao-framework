<?php
declare(strict_types=1);

namespace Yao\Facade;

/**
 * @method static render(string $template, $params = [])
 * Class View
 * @package Yao\Facade
 */
class View extends Facade
{

    protected static function getFacadeClass()
    {
        return 'view';
    }

}