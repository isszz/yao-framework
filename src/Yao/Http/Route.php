<?php

declare(strict_types=1);

namespace Yao\Http;

use Yao\App;
use Yao\Config;
use Yao\Exception\RouteNotFoundException;

/**
 * 路由操作类
 * Class Route
 * @package Yao
 */
class Route
{

    /**
     * 容器实例
     * @var App
     */
    protected App $app;

    /**
     * 请求实例
     * @var mixed|object|Request
     */
    protected Request $request;

    /**
     * Config配置实例
     * @var mixed|object|Config
     */
    protected Config $config;

    /**
     * 响应实例
     * @var mixed|object|Response
     */
    protected Response $response;

    /**
     * 路由注册树
     * @var array
     */
    protected array $routes = [
        'get' => [],
        'post' => [],
        'put' => [],
        'delete' => [],
        'patch' => [],
        'head' => []
    ];

    /**
     * 当前请求的控制器
     * @var string
     */
    public $controller = 'App\\Http\\Controllers';

    /**
     * 当前请求的方法
     * @var string
     */
    public string $action = '';

    /**
     * 路由传递的参数
     * @var array
     */
    public array $param = [];

    /**
     * 注册路由的方法
     * @var
     */
    private $method;

    /**
     * 注册路由的path
     * @var string
     */
    private string $path = '';

    /**
     * 路由注册的地址
     * @var
     */
    private $location;


    /**
     * 初始化实例列表
     * Route constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $app['request'];
        $this->config = $app['config'];
        $this->response = $app['response'];
    }

    /**
     * 路由注册
     * @param $method
     * @param $arguments
     * @return $this
     */
    public function __call($method, $arguments)
    {
        if (array_key_exists($method, $this->routes)) {
            $this->_setRoute($method, ...$arguments);
            return $this;
        }
        throw new RouteNotFoundException('Method Not Allowed: ' . $method);
    }

    /**
     * 重定向路由
     * @param string $uri
     * @param string $location
     * @param int $code
     * @param array|string[] $methods
     * @return $this
     */
    public function redirect(string $uri, string $location, int $code = 302, array $methods = ['get'])
    {
        //可以让路由传递参数给闭包
        $this->_setRoute($methods, $uri, function () use ($code, $location) {
            return redirect($location, $code);
        });
        return $this;
    }

    /**
     * 视图路由
     * @param string $uri
     * @param string $view
     * @param array $data
     * @param array|string[] $methods
     * @return $this
     */
    public function view(string $uri, string $view, array $data = [], array $methods = ['get'])
    {
        $this->_setRoute($methods, $uri, function () use ($view, $data) {
            return view($view, $data);
        });
        return $this;
    }

    /**
     * @param string $uri
     * @param $location
     * @param array|string[] $requestMethods
     * @return $this
     */
    public function rule(string $uri, $location, array $requestMethods = ['get', 'post']): Route
    {
        $this->_setRoute($requestMethods, $uri, $location);
        return $this;
    }

    /**
     * 未匹配到路由
     * @param \Closure $closure
     * @param array $data
     * @return $this
     */
    public function none(\Closure $closure, $data = [])
    {
        $this->routes['none'] = ['route' => $closure, 'data' => $data];
        return $this;
    }

    /**
     * 路由中间件注册方法
     * @param $middleware
     * @return $this
     */
    public function middleware($middleware)
    {
        $this->app['middleware']->setRouteMiddlewares($this->method, $this->path, $middleware);
        return $this;
    }

    /**
     * 路由别名设置
     * @param $name
     * 路由别名
     * @return $this
     */
    public function alias(string $name): Route
    {
        $this->app['alias']->set($name, $this->path);
        return $this;
    }


    /**
     * 路由允许跨域设置
     * @param null $AllowOrigin
     * 允许跨域域名
     * @param null $AllowCredentials
     * 是否可以将对请求的响应暴露给页面
     * @param null $AllowHeaders
     * 允许的头信息
     * @return $this
     */
    public function cors($allowOrigin = null, ?bool $allowCredentials = null, $allowHeaders = null, $allowAge = 600): Route
    {
        //需要判断是否存在配置，不存在则默认
        $cors = $this->config->get('cors');
        $allowOrigin || $allowOrigin = $cors['origin'];
        $allowHeaders || $allowHeaders = $cors['headers'];
        $allowAge || $allowAge = $cors['max_age'];
        isset($allowCredentials) || $allowCredentials = $cors['credentials'];
        $allowCredentials = $allowCredentials ? 'true' : 'false';
        foreach ((array)$this->method as $method) {
            $this->routes[$method][$this->path]['cors'] = [
                'origin' => $allowOrigin,
                'credentials' => $allowCredentials,
                'headers' => $allowHeaders
            ];
        }
        return $this;
    }

    private function _setRoute($method, $uri, $location)
    {
        [$this->method, $this->path, $this->location] = [$method, '/' . trim($uri, '/'), $location];
        foreach ((array)$this->method as $method) {
            $this->routes[$method][$this->path]['route'] = $location;
        }
    }

    public function dispatch()
    {
        $this->allowCors();
        $method = $this->request->method();
        $path = $this->request->path();
        $dispatch = null;
        if ($this->hasRoute($method, $path)) {
            $dispatch = $this->getRoutes($method, $path);
        } else {
            foreach ($this->withMethod($method) as $uri => $location) {
                //设置路由匹配正则
                $uriRegexp = '#^' . $uri . '$#iU';
                //路由和请求一致或者匹配到正则
                if (preg_match($uriRegexp, $path, $match)) {
                    //如果是正则匹配到的uri且有参数传入则将参数传递给成员属性param
                    if (isset($match)) {
                        array_shift($match);
                        $this->param = $match;
                    }
                    $dispatch = $location['route'];
                    break;
                }
            }
        }
        if (is_null($dispatch)) {
            if (!isset($this->routes['none'])) {
                throw new RouteNotFoundException('Page not found: ' . $path, 404);
            }
            $this->param = $this->routes['none']['data'];
            $dispatch = $this->routes['none']['route'];
        }
        if (is_array($dispatch) && 2 == count($dispatch)) {
            $this->request->controller($dispatch[0]);
            $this->request->action($dispatch[1]);
            [$this->controller, $this->action] = $dispatch;
        } else if (is_string($dispatch)) {
            $controller = explode('/', $dispatch);
            $this->action = array_pop($controller);
            $this->request->action($this->action);
            foreach ($controller as $directory) {
                $this->controller .= '\\' . ucfirst($directory);
            }
            $this->request->controller($this->controller);
        } else {
            $this->controller = $dispatch;
        }

        return $this->app['middleware']->make(function () {
            return $this->_dispatch();
        }, 'route');

    }

    private function _dispatch()
    {
        if ($this->controller instanceof \Closure) {
            $response = $this->controller;
        } else if (is_string($this->controller)) {
            $this->app->make($this->controller);
            $response = function () {
                return $this->app->invokeMethod([$this->controller, $this->action], $this->param);
            };
        }
//        return function () use ($response) {
        return $this->app['middleware']->make($response, 'controller');
//        };
    }


    /**
     * 跨域支持
     */
    public function allowCors()
    {
        if (isset($this->routes[$this->request->method()][$this->request->path()]['cors'])) {
            $allows = $this->routes[$this->request->method()][$this->request->path()]['cors'];
            $origin = $allows['origin'] ?? $this->config->get('cors.origin');
            $credentials = $allows['credentials'] ?? ($this->config->get('cors.credentials') ? 'true' : 'false');
            $headers = $allows['headers'] ?? $this->config->get('cors.headers');
            $age = $allows['max_age'] ?? $this->config->get('cors.max_age');
            header('Access-Control-Allow-Origin:' . $origin);
            header('Access-Control-Allow-Credentials:' . $credentials);
            header('Access-Control-Allow-Headers:' . $headers);
            header('Access-Control-Max-Age:' . $age);
        } else if ('options' == $this->request->method()) {
            //需要优化下，解决了其他请求方式下的跨域问题
            $allows = $this->routes[$this->request->method()][$this->request->path()]['cors'];
            $origin = $allows['origin'] ?? $this->config->get('cors.origin');
            $credentials = $allows['credentials'] ?? ($this->config->get('cors.credentials') ? 'true' : 'false');
            $headers = $allows['headers'] ?? $this->config->get('cors.headers');
            $age = $allows['max_age'] ?? $this->config->get('cors.max_age');
            header('Access-Control-Allow-Origin:' . $origin);
            header('Access-Control-Allow-Credentials:' . $credentials);
            header('Access-Control-Max-Age:' . $age);
            header('Access-Control-Allow-Headers:' . $headers, true, 204);
            exit;
        }
    }

    private function hasRoute($method, $path)
    {
        return isset($this->routes[$method][$path]['route']);
    }

    private function getRoutes($method, $path)
    {
        return $this->routes[$method][$path]['route'];
    }

    private function withMethod($method)
    {
        if (!isset($this->routes[$method])) {
            throw new RouteNotFoundException('Method not allowed: ' . $method);
        }
        return (array)$this->routes[$method];
    }

    /**
     * 获取路由列表
     * @param null $requestMethod
     * @param null $requestPath
     * @return array|mixed
     */
    public function getRoute($requestMethod = null, $requestPath = null)
    {
        return $requestPath ? $this->routes[$requestMethod][$requestPath] : ($requestMethod ? $this->routes[$requestMethod] : $this->routes);
    }


    /**
     * 路由注册方法
     */
    public function register()
    {
        if (file_exists($routes = env('storage_path') . 'cache' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'routes.php')) {
            $this->routes = unserialize(file_get_contents($routes));
        } else {
            $files = env('routes_path') . '*' . 'php';
            array_map(
                fn($routes) => require_once($routes),
                glob($files)
            );
        }
    }
}
