<?php


namespace Yao\Concerns;

/**
 * 单例Trait
 * Trait SingleInstance
 * @package Yao\Concerns
 */
trait SingleInstance
{

    protected static ?object $instance;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function instance()
    {
        if (!static::$instance instanceof static) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function __destruct()
    {
        static::$instance = null;
    }
}