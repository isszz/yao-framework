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

    /**
     * 路由中间件
     * @var array
     */
    protected array $route = [];

    /**
     * 控制器中间件
     * @var array
     */
    protected array $controller = [];

    /**
     * Middleware constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }


    public function getByRoute(string $route): array
    {
        if (isset($this->route[$route])) {
            return (array)$this->route[$route];
        }
        return [];
    }


    public function getByController($controller)
    {

    }

    /**
     * 中间件注册方法
     * @var array
     */
    public $middleware = [];

    public function handle($request, \Closure $next)
    {
    }


    public function make()
    {
    }


    public function before()
    {
    }


    public function after()
    {
    }

    public function middleware($request, \Closure $closure)
    {
    }

    public function set($middleware, $method, $path)
    {
        $this->middleware[$method][$path] = [...($this->middleware[$method][$path] ?? []), ...(array)$middleware];
    }

    public function get()
    {
        return $this->middleware[\Yao\Facade\Request::method()][\Yao\Facade\Request::path()];
    }
}
