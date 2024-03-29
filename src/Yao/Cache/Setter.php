<?php
declare(strict_types=1);

namespace Yao\Cache;

use Yao\App;

/**
 * @method get(string $key) 查询缓存
 * Class Setter
 * @package Yao\Cache
 */
class Setter
{
    public $driver;

    public function __construct(App $app)
    {
        $this->app = $app;
        $driver = '\\Yao\\Cache\\Drivers\\' . ucfirst($app->config->get('cache.default'));
        $this->driver = $app->make($driver, [$app->config->getDefault('cache')]);
    }

    /**
     * @param $cacheCommand
     * @param $data
     * @return mixed
     */
    public function __call($cacheCommand, $data)
    {
        return $this->driver->$cacheCommand(...$data);
    }
}
