<?php


namespace Yao\Http;


use Yao\Traits\SingleInstance;

class Middleware
{

    use SingleInstance;

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