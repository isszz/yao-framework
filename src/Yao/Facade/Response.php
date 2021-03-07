<?php
declare(strict_types=1);

namespace Yao\Facade;

/**
 * @method static \Yao\Http\Response data($data)
 * @method static \Yao\Http\Response code(int $code)
 * @method static \Yao\Http\Response header(string|array $header)
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
