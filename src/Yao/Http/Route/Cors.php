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

    protected array $cors = [];

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $app['request'];
        $this->response = $app['response'];
    }

    public function setHeader($header,$value){
        $this->cors[$header] = $value;
    }


    public function hasHeader($header){
        return array_key_exists($header,$this->cors);
    }

    public function allow()
    {
        if($this->hasHeader('Access-Control-Allow-Origin')){
            foreach($this->cors as $header => $value){
                $this->response->header($header. ':'. $value);
            }
            if ($this->request->isMethod('options')) {
                return $this->response->code(204)->send();
            }
        }
    }

    public function setAllowOrigin($origin)
    {
        if ('*' == $origin) {
            $this->setHeader('Access-Control-Allow-Origin','*');
        } else if (in_array($allowOrigin = $this->request->header('origin'), (array)$origin)) {
            $this->setHeader('Access-Control-Allow-Origin',$allowOrigin);
        }
        return $this;
    }

    public function setAllowHeaders($allowHeaders)
    {
        $this->setHeader('Access-Control-Allow-Headers',$allowHeaders);
        return $this;
    }

    public function setCredentials($allowCredentials)
    {
        $this->setHeader('Access-Control-Allow-Credentials',$allowCredentials);
        return $this;
    }

    public function setAllowMethod(string $method)
    {
        $this->setHeader('Access-Control-Allow-Methods',strtoupper($method));
        return $this;
    }

    public function setMaxAge(int $maxAge)
    {
        $this->setHeader('Access-Control-Max-Age:',$maxAge);
        return $this;
    }

}
