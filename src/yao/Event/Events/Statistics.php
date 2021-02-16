<?php


namespace Yao\Event\Events;


class Statistics
{
    public function trigger()
    {
        //[debug] 这里可以使用事件，获取注册到该位置的事件执行
        if (defined('START_TIME')) {
            $timeConsuming = round(microtime(true) - START_TIME,4).'s';
//            echo $timeConsuming . '<br>';
        }
        if (defined('START_MEMORY_USAGE')) {
            $usedMemory = round(((memory_get_usage() - START_MEMORY_USAGE) / 1024 / 1024), 2) . 'MB';
//            echo $usedMemory;
        }
    }
}