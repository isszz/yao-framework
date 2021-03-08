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
    
    protected int $retryTimes = 0;

    public function __construct($config, \Redis $redis)
    {
        $this->redis = $redis;
        $this->connect();
        isset($config['auth']) && $this->redis->auth($config['auth']);
    }

    public function connect(){
        $this->retryTimes++;
        try{
            $this->redis->connect($config['host'] ?? '127.0.0.1', $config['port'] ?? 6379, $config['timeout'] ?? 5);
        }catch (\Exception $e){
            if(3 == $this->retryTimes){
                throw new \Exception('Conection failed for 3 times');
            }
            return $this->connect();
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
