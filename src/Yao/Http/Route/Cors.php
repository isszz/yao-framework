<?php


namespace Yao\Http\Route;

use Yao\App;
use Yao\Http\Request;
use Yao\Http\Response;

/**
 * 跨域支持类
 * Class Cors
 * @package Yao\Route
 */
class Cors
{

    protected App $app;

    protected Request $request;

    protected Response $response;

    protected array $cors = [];

    protected array $defaultRule = [];

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $app['request'];
        $this->response = $app['response'];
        $this->defaultRule = $app->config->get('cors');
    }

    public function allow()
    {
        //正则路由不起作用
        if ($this->request->isMethod('options')) {
            return $this->response
                ->header($this->cors[$this->request->method()][$this->request->path()])
                ->code(204)
                ->send();
        }
        if (isset($this->cors[$this->request->method()][$this->request->path()])) {
            return $this->response
                ->header($this->cors[$this->request->method()][$this->request->path()]);
        }
    }


    public function set($methods, $path, $options)
    {
        foreach ((array)$methods as $method) {
            $this->cors[$method][$path] = $options;
        }
    }

}