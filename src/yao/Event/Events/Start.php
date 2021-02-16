<?php


namespace Yao\Event\Events;


class Start
{
    public function trigger()
    {
        set_time_limit(30);
        @ini_set('memory_limit', '64M');
        define('START_TIME', microtime(true));
        define('START_MEMORY_USAGE', memory_get_usage());
    }
}