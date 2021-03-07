<?php

namespace Yao\Http;

use Yao\App;

/**
 * 响应类
 * Class Response
 * @package Yao\Http
 */
class Response
{

    /**
     * 容器实例
     * @var App
     */
    protected App $app;

    /**
     * 响应状态码
     * @var int
     */
    protected int $code = 200;

    /**
     * 响应头信息
     * @var array|string[]
     */
    protected array $header = ['Content-Type:text/html; charset=UTF-8', 'X-Powered-By:YaoPHP'];

    /**
     * 响应的额外数据
     * @var
     */
    protected $data;

    /**
     * 初始化容器实例
     * Response constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 添加响应数据
     * @param \Closure|array|string $data
     * @return $this
     */
    public function data($data)
    {
        if ($data instanceof \Closure) {
            return $this->data($data());
        }
        if (is_array($data)) {
            $this->header('Content-Type:application/json; charset=UTF-8');
            $data = json_encode($data, 256);
        }
        $this->data = $data;
        return $this;
    }

    /**
     * 设置响应状态码
     * @param null|int $code
     * @return $this
     */
    public function code(?int $code = null)
    {
        isset($code) && $this->code = $code;
        return $this;
    }

    /**
     * 设置响应头
     * @param string|array $header
     * 头信息
     * @return $this
     */
    public function header($header)
    {
        $this->header = [...$this->header, ...(array)$header];
        return $this;
    }

    /**
     * 响应执行
     */
    public function send()
    {
        foreach ($this->header as $header) {
            header($header);
        }
        http_response_code($this->code);
        echo $this->data;
        ob_end_flush();
        exit;
    }

    public function __destruct()
    {
        $this->app['event']->trigger('response_sent');
    }

}
