<?php


namespace Yao\Cache;

use Psr\Cache\CacheItemInterface;
use Yao\Concerns\SingleInstance;

abstract class Driver implements CacheItemInterface
{
    use SingleInstance;

    public function get(string $key)
    {
    }
}
