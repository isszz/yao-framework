<?php
declare(strict_types=1);

namespace Yao\Http;

use Yao\App;

class Middleware
{

    /**
     * 容器实例
     * @var App
     */
    protected App $app;


    protected array $route = [];

    protected array $controller = [];


    /**
     * Middleware constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function setRouteMiddlewares($middleware)
    {
        $this->route = (array)$middleware;
    }

    public function setControllerMiddlewares(array $middleware)
    {
        $this->controller = $middleware;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getController()
    {
        $middlewares = [];
        foreach ($this->controller as $middleware => $actions) {
            if (is_string($actions)) {
                if ('*' == $actions) {
                    $middlewares[] = $middleware;
                }
            } else if (in_array($this->app->request->action(), $actions)) {
                $middlewares[] = $middleware;
            }
        }
        return $middlewares;
    }

    /**
     * 需要用pipeline模式重写
     * @param $request
     * @param $type
     * @return \Closure
     * @throws \Exception
     */
    public function make($request, $type)
    {
        $middlewares = [];
        switch ($type) {
            case 'route':
                $middlewares = (array)$this->getRoute();
                break;
            case 'controller':
                $middlewares = $this->getController();
                break;
            case 'global':
                $middlewares = (array)$this->app->config->get('app.middleware');
                break;
            default:
                throw new \Exception('不能调度中间件');
        }
        if (!empty($middlewares)) {
            foreach ($middlewares as $middleware) {
                $request = function () use ($middleware, $request) {
                    return $this->app->invokeMethod([$middleware, 'handle'], [$request, function ($request) {
                        return $request();
                    }], false);
                };
            }
        }
        return $request;
    }

}
