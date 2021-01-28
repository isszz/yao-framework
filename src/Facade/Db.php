<?php


namespace Yao\Facade;

use Yao\Container;
use Yao\Facade;

/**
 * Class Db
 * @package Yao\Facade
 * @method static \Yao\Db\Driver name(string $table_name) 表名设置方法
 * @method static array|false query(string $sql, ?array $data = [], bool $all = true)
 * @method static integer exec(string $sql, array $data = [])
 */
class Db extends Facade
{
    protected static function getFacadeClass()
    {
        return \Yao\Db\Query::class;
    }

}