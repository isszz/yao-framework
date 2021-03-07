<?php
declare(strict_types=1);

namespace Yao\Cache\Drivers;

use Yao\Cache\Driver;

class File extends Driver
{

    /**
     * 缓存路径
     * @var string
     */
    protected string $path;

    public function __construct()
    {
        $this->path = env('cache_path') . 'app' . DIRECTORY_SEPARATOR;
        if (!file_exists($this->path)) {
            mkdir($this->path, 0777, true);
        }
    }

    public function has($key)
    {
        return file_exists($this->path . strtolower($key));
    }

    public function get(string $key)
    {
        if ($this->has($key)) {
            return file_get_contents($this->path . strtolower($key));
        }
        throw new \InvalidArgumentException('Cache not found: ' . $key, 999);
    }

    public function set($key, $value)
    {
        file_put_contents($this->path . strtolower($key), $value);
    }
}
