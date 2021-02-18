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


    protected array $middleware = [];

    /**
     * Middleware constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }


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


    public function push()
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
