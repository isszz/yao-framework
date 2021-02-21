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
        switch ($type) {
            case 'route':
                $dispatch = (array)$this->getRoute($this->app->request->method(), $this->app->request->path());
                break;
            case 'controller':
                $dispatch = $this->getController();
                break;
            case 'global':
                $dispatch = (array)$this->app->config->get('app.middleware');
                break;
            default:
                throw new \Exception('不能调度中间件');
        }
//        return $this->pipeline($dispatch, $request);
        $return = $request;
        if (!empty($dispatch)) {
            foreach ($dispatch as $middleware) {
                $return = function () use($middleware,$request) {
                    return (new $middleware())->handle($request, function ($request) {
                        return $request;
                    });
                };
            }
        }
        return $return;
    }

    public function pipeline($array, $request)
    {
        static $return;
        if (!empty($array)) {
            $middleware = array_shift($array);
            $return = function () use ($request, $middleware) {
                return (new $middleware())->handle($request, function ($request) {
                    return $request;
                });
            };
            return $this->pipeline($array, $return);
        }
        return $return;
    }

}
