<?php


namespace Yao\Facade;

use Yao\Facade;

/**
 * Class Db
 * @package Yao\Facade
 * @method static \Yao\Database\Driver name(string $table_name) 表名设置方法
 * @method static \Yao\Database\Driver table(string $table_name) 表名设置方法
 * @method static array|false query(string $sql, ?array $data = [], bool $all = true)
 * @method static integer exec(string $sql, array $data = [])
 */
class Db extends Facade
{
    protected static $singleInstance = false;

    protected static function getFacadeClass()
    {
        return \Yao\Database\Query::class;
    }

    public static function __callStatic($method, $params)
    {
        return static::createFacade()->$method(...$params);
//        return call_user_func_array([static::createFacade(), $method], $params);
    }


}