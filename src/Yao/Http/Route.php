<?php
declare(strict_types=1);

namespace Yao\Http;

use Yao\App;
use Yao\Config;
use Yao\Exception\RouteNotFoundException;
use Yao\Http\Route\Cors;

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
     * @param string|array $middleware
     * 中间件完整类名字符串或者索引数组
     * @return $this
     */
    public function middleware($middleware)
    {
        foreach ((array)$this->method as $method) {
            if ($method == $this->request->method() && ($this->path == $this->request->path() || preg_match("#^{$this->path}$#iU", $this->request->path()))) {
                $this->app['middleware']->setRouteMiddlewares($middleware);
            }
        }
        return $this;
    }

    /**
     * 路由别名设置
     * @param string $name
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
     * @param string $allowOrigin
     * 允许跨域域名
     * @param string $allowCredentials
     * 是否可以将对请求的响应暴露给页面
     * @param string $allowHeaders
     * 允许的头信息
     * @param int $allowAge
     * 缓存预检时间
     * @return $this
     */
    public function cors($allowOrigin = '*', string $allowCredentials = 'true', string $allowHeaders = 'Origin,Content-Type,Accept,token,X-Requested-With', int $maxAge = 600): Route
    {
        if ($this->request->path() == $this->path || preg_match("#^{$this->path}$#iU", $this->request->path())) {
            $this->app[Cors::class]
                ->setOrigin($allowOrigin)
                ->setAllowHeaders($allowHeaders)
                ->setCredentials($allowCredentials)
                ->setMaxAge($maxAge)
                ->setAllowMethod($this->request->method())
                ->allow();
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

        return $this->app['middleware']->make($this->_dispatch(), 'route');

    }

    private function _dispatch()
    {
        if ($this->controller instanceof \Closure) {
            $request = $this->controller;
        } else if (is_string($this->controller)) {
            $this->app->make($this->controller);
            $request = function () {
                return $this->app->invokeMethod([$this->controller, $this->action], $this->param);
            };
        }
        return $this->app['middleware']->make($request, 'controller');
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
