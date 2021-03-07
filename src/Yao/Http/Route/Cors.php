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
        if ($this->request->isMethod('options')) {
            return $this->response
                ->header($this->cors)
                ->code(204)
                ->send();
        }
        if (!empty($this->cors)) {
            return $this->response
                ->header($this->cors);
        }
    }


    public function set($options)
    {
        $this->cors = $options;
    }

}