<?php

namespace Yao;

use Yao\Exception\ErrorException;

/**
 * 错误和异常注册类
 * Class Error
 * @package Yao
 */
class Error
{

    protected bool $debug;
    protected string $exceptionView;

    /**
     * 日志实例
     * @var mixed|Log
     */
    protected Log $log;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->log = $app['log'];
        $this->debug = $this->app['config']->get('app.debug');
        $this->exceptionView = $this->app['config']->get('app.exception_view') ?: dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Tpl' . DIRECTORY_SEPARATOR . 'exception.html';
    }

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

    public function exception($exception)
    {
        $code = $exception->getCode() ?: 'Exception';
        $message = $exception->getMessage();
        $this->log->write('Exception', $message, 'notice', ['Method' => $this->app['request']->method(), 'Path' => $this->app['request']->path()]);
        if ($this->debug) {
            $data = '<!DOCTYPE html>
            <html lang="zh">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>' . $message . '</title>
            </head>
            <body>
            <b>Message:</b> ' . $message . '
             <br><b>Code:</b>' . $code . '<br><b> File:</b> ' . $exception->getFile() . '<br ><b > Line:</b > ' . $exception->getLine() . '<pre style = "font-size:1.4em;margin-top: .5em" >' . $exception->getTraceAsString() . '</pre>
            </body >
            </html > ';
        } else {
            $data = include_once $this->exceptionView;
        }
        exit($this->app['response']->data($data)->code((int)$exception->getCode())->return());
    }

    public function error($code, $message, $file, $line, $errContext)
    {
        $this->log->write('Error', $message, 'notice', ['Method' => $this->app['request']->method(), 'Path' => $this->app['request']->path(), $code, $file, $line]);
        throw new ErrorException($message, $code);
    }

    public function shutdown()
    {
        if ($error = error_get_last()) {
            $this->log->write('Fetal', $error, 'notice', ['Method' => $this->app['request']->method(), 'Path' => $this->app['request']->path()]);
            throw new \Exception($error);
        }
    }
}
