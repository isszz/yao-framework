<?php


namespace Yao\Cache;

use Yao\App;

/**
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

    public function __call($cacheCommand, $data)
    {
        return $this->driver->$cacheCommand(...$data);
    }
}
