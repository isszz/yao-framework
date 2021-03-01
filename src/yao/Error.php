<?php
declare(strict_types=1);

namespace Yao;

use Yao\Exception\ErrorException;
use Yao\Http\{Request, Response};

/**
 * 错误和异常注册类
 * Class Error
 * @package Yao
 */
class Error
{

    /**
     * 容器实例
     * @var App
     */
    protected App $app;

    /**
     * 请求对象
     * @var mixed|object|Request
     */
    protected Request $request;

    /**
     * 响应实例
     * @var mixed|object|Response
     */
    protected Response $response;

    /**
     * 日志实例
     * @var mixed|Log
     */
    protected Log $log;

    /**
     * 调试模式开关
     * @var bool
     */
    protected bool $debug;

    /**
     * 异常视图模板文件
     * @var array|mixed|string
     */
    protected string $exceptionView;


    /**
     * 初始化实例列表和参数
     * Error constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->log = $app['log'];
        $this->request = $app['request'];
        $this->response = $app['response'];
        $this->debug = $this->app['config']->get('app.debug');
        $this->exceptionView = $this->app->config->get('app.exception_view') ?: dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR . 'exception.html';
    }

    /**
     * 错误和异常注册
     */
    public function register()
    {
        $iniSet = [
            [true => 'On', false => 'Off'],
            [true => E_ALL, false => 0]
        ];
        function_exists('ini_set') && ini_set('display_errors', $iniSet[0][$this->debug]);
        error_reporting($iniSet[1][$this->debug]);
        set_error_handler([$this, 'error']);
        set_exception_handler([$this, 'exception']);
        register_shutdown_function([$this, 'shutdown']);
    }

    /**
     * 异常回调函数
     * @param $exception
     */
    public function exception($exception)
    {


        $code = $exception->getCode() ?? 'Exception';
        $message = $exception->getMessage();
        $this->log->write('Exception', $message, 'notice', ['Method' => $this->request->method(), 'URL' => $this->request->url(true), 'ip' => $this->request->ip()]);
        if ($this->debug) {
            echo '<meta name="viewport"  content="width=device-width, initial-scale=1.0"><body style="width:90vw;margin: 0 auto;border:1px solid #d5d1d1;margin: .5em auto"><div style="background-color: #1E90FF;line-height:3em;padding:0 1em;height: 3em;color: white;font-weight: bold">Message: ' . $message . '</div><pre style="margin-top:0;padding:.5em;font-size: 1.5em;display: block;word-wrap: break-word;word-break: break-all;white-space:break-spaces">';
            echo '';
            echo '<p><b>File: </b>' . $exception->getFile() . ' +' . $exception->getLine() . '</p>';
            echo '<p><b>Code: </b>' . $code . '</p>';
            for ($key = 0; $key <= count($exception->getTrace()) - 2; $key++) {
                $errorFile = $exception->getTrace()[$key]['file'];


                $file = file($exception->getTrace()[$key]['file']);
                $line = $exception->getTrace()[$key]['line'];
                $function = $exception->getTrace()[$key]['function'];
                echo '<p style="background-color: #65adf3;color: white">' . $errorFile . ' +' . $line . '</p>';
                for ($i = $line - 4; $i < $line + 3 && $i < count($file); $i++) {
                    $code = $file[$i];
                    echo '<span style="background-color: #EEEEEE;color: grey">' . str_pad((string)($i + 1), 3, ' ', STR_PAD_BOTH) . '</span>';
                    if ($i + 1 == $line) {
                        $code = '<text style="width:100%;background-color: #eeeeee">' . str_replace($function, '<span style="color: red">' . $function . '</span>', $file[$i]) . '</text>';
                    }
                    echo $code;
                }
            }
            echo '</pre><div style="text-align:right;background-color: #1E90FF;line-height:3em;padding:0 1em;height: 3em;color: white;font-weight: bold">Yao - 一款轻量的PHP框架！<a href="https://github.com/topyao/yao">Github</a>&nbsp;&nbsp<a href="https://packagist.org/packages/chengyao/yao">Packagist</a></div></body>';
        } else {
            include_once $this->exceptionView;
        }
        return $this->response->code((int)$code)->send();
    }

    /**
     * 错误回调函数
     * @param $code
     * @param $message
     * @param $file
     * @param $line
     * @param $errContext
     * @throws ErrorException
     */
    public function error($code, $message, $file, $line, $errContext)
    {
        $this->log->write('Error', $message, 'notice', ['Method' => $this->request->method(), 'URL' => $this->request->url(true), 'ip' => $this->request->ip(), $code, $file, $line]);
        throw new ErrorException($message, $code);
    }

    /**
     * 脚本终止回调函数
     * @throws \Exception
     */
    public function shutdown()
    {
        if ($error = error_get_last()) {
            $this->log->write('Fetal', $error, 'notice', ['Method' => $this->request->method(), 'URL' => $this->request->url(true)]);
            throw new \Exception($error);
        }
    }
}
