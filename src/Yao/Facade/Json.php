<?php
declare(strict_types=1);

namespace Yao\Facade;

/**
 * Class Json
 * @package Yao\Facade
 * @method static \Yao\Http\Response\Json data($data)
 */
class Json extends Facade
{
    protected static function getFacadeClass()
    {
        return \Yao\Http\Response\Json::class;
    }
}
