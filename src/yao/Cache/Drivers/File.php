<?php

namespace Yao\Cache\Drivers;

use Yao\Cache\Driver;

class File extends Driver
{
    public function get(string $key)
    {
        return file_get_contents();
    }

    public function set($key,$value)
    {

    }
}
