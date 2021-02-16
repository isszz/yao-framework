<?php

namespace Yao\Event;

use Yao\Facade\Config;

class Event
{

    protected array $events = [
        'app_start' => [
        ],
        'response_sent' => [
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
        $triggers = array_merge($this->get($trigger), Config::get('app.events.' . $trigger,[]));
        foreach ($triggers as $event) {
            (new $event)->trigger();
        }
    }
}
