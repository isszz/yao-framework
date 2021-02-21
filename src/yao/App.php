<?php

declare(strict_types=1);

namespace Yao;

use App\Http\Validate;
use Yao\Http\{Middleware, Request, Response, Route, Route\Alias, Session};
use Yao\Cache\Setter;
use Yao\Event\Event;
use Yao\View\Render;

/**
 * @property Request $request
 * @property Validate $validate
 * @property Env $env
 * @property Config $config
 * @property Render $view
 * @property Response $response
 * @property Session $session
 * @property Log $log
 * @property Cache $cache
 * @property Event $event
 * @property Middleware $middleware
 * Class App
 * @package Yao
 */
class App extends Container
{
    /**
     * 绑定的类名
     * @var array|string[]
     */
    protected array $bind = [
        'cache' => Setter::class,
        'request' => Request::class,
        'validate' => Validate::class,
        'file' => File::class,
        'app' => App::class,
        'env' => Env::class,
        'config' => Config::class,
        'view' => Render::class,
        'route' => Route::class,
        'error' => Error::class,
        'response' => Response::class,
        'session' => Session::class,
        'log' => Log::class,
        'event' => Event::class,
        'alias' => Alias::class,
        'middleware' => Middleware::class
    ];

    public function run()
    {
        set_time_limit(30);
        function_exists('ini_set') && ini_set('memory_limit', '64M');
        $this['error']->register();
        $this['event']->trigger('app_start');
        //        ignore_user_abort(true);
        ob_start();
        if ($this['config']->get('app.auto_start')) {
            isset($_SESSION) || session_start();
            // $this['session']->flashCheck();
        }
        date_default_timezone_set($this->config->get('app.default_timezone', 'PRC'));
        $this->bind = array_merge((array)$this->config->get('app.alias'), $this->bind);
        $this['route']->register();
        return $this->response->data(
            $this['middleware']->make(function () {
                return $this->route->dispatch();
            }, 'global'))->send();
//        $this->route->dispatch();
    }
}
