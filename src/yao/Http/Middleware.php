<?php


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

    public function setRouteMiddlewares($method, $path, $middleware)
    {
        foreach ((array)$method as $m) {
            $this->route[$method][$path] = $middleware;
        }
    }

    public function setControllerMiddlewares(array $middleware)
    {
        $this->controller = $middleware;
    }

    public function getRoute($method, $path)
    {
        return $this->route[$method][$path] ?? [];
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

    public function make($request, $type)
    {
        $dispatch = [];
        if ('route' == $type) {
            $dispatch = (array)$this->getRoute($this->app->request->method(), $this->app->request->path());
        } else if ('controller' == $type) {
            $dispatch = $this->getController();
        } else if ('global' == $type) {
            $dispatch = (array)$this->app->config->get('app.middleware');
        }
        $return = $request;
        if (!empty($dispatch)) {
            foreach ($dispatch as $middleware) {
                $return = (new $middleware())->handle($request, function ($request) {
                    return $request;
                });
            }
        }
        return $return;
    }

}
