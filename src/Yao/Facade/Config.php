<?php
declare(strict_types=1);

namespace Yao\Facade;

/**
 * @method static get(string $key = null, $default = null) 获取配置
 * @method static load(string $config) 加载配置文件
 * @method static getDefault(string $type) 按照type获取配置
 * Class Config
 * @package Yao\Facade
 */
class Config extends Facade
{

    protected static function getFacadeClass()
    {
        return 'config';
    }
}
