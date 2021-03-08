<?php
declare(strict_types=1);

namespace Yao\Cache\Drivers;

use Yao\Cache\Driver;

/**
 * Class Memcached
 * @package Yao\Cache\Drivers
 */
class Memcached extends Driver
{

    private \Memcached $memcached;

    public function __construct($config, \Memcached $memcached)
    {
        $this->memcached = $memcached;
        $this->memcached->addServer($config['host'] ?? '127.0.0.1', $config['post'] ?? 11211);
    }

    public function get(string $key)
    {
        return $this->memcached->get($key);
    }

    public function set(string $key, $value)
    {
        return $this->memcached->set($key, $value);
    }

    public function has(string $key)
    {
    }


}
