<?php
declare(strict_types=1);

namespace Yao\Cache\Drivers;

use Yao\Cache\Driver;

/**
 * Class Redis
 * @package Yao\Cache\Drivers
 */
class Redis extends Driver
{

    protected \Redis $redis;

    public function __construct($config)
    {
        $this->redis = new \Redis();
        $this->redis->connect($config['host'] ?? 'localhost', $config['port'] ?? 6379, $config['timeout'] ?? 10);
        if ($config['auth']) {
            $this->redis->auth($config['auth']);
        }
    }

    /**
     * @return \Redis
     */
    public function handle()
    {
        return $this->redis;
    }

    public function set(string $key, $value, ?int $timeout = null)
    {
        return $this->redis->set($key, $value, $timeout);
    }

    public function get(string $key)
    {
        return $this->redis->get($key);
    }

    public function has(string $key)
    {
        return $this->redis->exists($key);
    }
}