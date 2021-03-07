<?php
declare(strict_types=1);

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

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $app['request'];
        $this->response = $app['response'];
    }

    public function allow()
    {
        if ($this->request->isMethod('options')) {
            return $response->code(204)->send();
        }
    }

    public function setOrigin($origin)
    {
        if ('*' == $origin) {
            $this->response->header('Access-Control-Allow-Origin: *');
        } else if (in_array($allowOrigin = $this->request->header('origin'), (array)$origin)) {
            $this->response->header('Access-Control-Allow-Origin:' . $allowOrigin);
        }
        return $this;
    }

    public function setAllowHeaders($allowHeaders)
    {
        $this->response->header('Access-Control-Allow-Headers:' . $allowHeaders);
        return $this;
    }

    public function setCredentials($allowCredentials)
    {
        $this->response->header('Access-Control-Allow-Credentials:' . $allowCredentials);
        return $this;
    }

    public function setAllowMethod(string $method)
    {
        $this->response->header('Access-Control-Allow-Methods:' . strtoupper($method));
        return $this;
    }

    public function setMaxAge(int $maxAge)
    {
        $this->response->header('Access-Control-Max-Age:' . $maxAge);
        return $this;
    }

}