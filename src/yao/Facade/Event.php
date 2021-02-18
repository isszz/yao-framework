<?php


namespace Yao\Facade;


/**
 * @method static trigger($trigger);
 * Class Event
 * @package Yao\Facade
 */
class Event extends Facade
{

    protected static function getFacadeClass()
    {
        return 'event';
    }

}