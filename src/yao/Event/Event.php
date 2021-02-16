<?php

namespace Yao\Event;

use Yao\Event\Events\Start;
use Yao\Event\Events\Statistics;

class Event
{

    protected array $events = [
        'app_start' => [
            Start::class
        ],
        'response_sent' => [
            Statistics::class
        ],
        'view_rendered' => [

        ]
    ];

    public function listen($trigger, $event)
    {
        if (!$this->has($trigger, $event)) {
            $this->events[$trigger][] = $event;
        } else {
            throw new \Exception('[debug,英语太蹩脚]Event has already listened at ' . $trigger . ' : ' . $event);
        }
    }

    public function has($trigger, $event)
    {
        return isset($this->events[$trigger][$event]);
    }

    public function get($trigger)
    {
        return $this->events[$trigger];
    }

    public function trigger($trigger)
    {
        foreach ($this->get($trigger) as $event) {
            (new $event)->trigger();
        }
    }
}
