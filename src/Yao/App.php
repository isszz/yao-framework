<?php
declare(strict_types=1);

namespace Yao;

use App\Http\Validate;
use Yao\{Cache\Setter, Database\Query, Event\Event, View\Render};
use Yao\Http\{Middleware, Request, Response, Route, Route\Alias, Session};

/**
 * @property Request $request 请求实例
 * @property Validate $validate 验证器实例
 * @property Env $env 环境变量实例
 * @property Config $config 配置类实例
 * @property Render $view 视图渲染类实例
 * @property Response $response 响应实例
 * @property Session $session Session实例
 * @property Log $log 日志类实例
 * @property Route $route 路由实例
 * @property Cache $cache 缓存类实例
 * @property Event $event 事件实例
 * @property Middleware $middleware 中间件实例
 * Class App
 * @package Yao
 */
class App extends Container
{
    /**
     * 绑定的类名
     * @var array|string[]
     */
    protected array $bind = [
        'cache' => Setter::class,
        'request' => Request::class,
        'validate' => Validate::class,
        'file' => File::class,
        'app' => App::class,
        'env' => Env::class,
        'config' => Config::class,
        'view' => Render::class,
        'route' => Route::class,
        'error' => Error::class,
        'response' => Response::class,
        'session' => Session::class,
        'log' => Log::class,
        'db' => Query::class,
        'event' => Event::class,
        'alias' => Alias::class,
        'middleware' => Middleware::class
    ];

    public function run()
    {
        set_time_limit(30);
        function_exists('ini_set') && ini_set('memory_limit', '64M');
        ob_start();
        //注册错误和异常
        $this['error']->register();
        //触发app_start事件
        $this['event']->trigger('app_start');
        //        ignore_user_abort(true);
        //Session自动开启
        if ($this['config']->get('app.auto_start')) {
            isset($_SESSION) || session_start();
            // $this['session']->flashCheck();
        }
        date_default_timezone_set($this->config->get('app.default_timezone', 'PRC'));
        //初始化绑定类
        $this->bind = array_merge((array)$this->config->get('app.alias'), $this->bind);
        //注册路由
        $this['route']->register();
        //发送响应
        return $this->response
            ->data($this['middleware']->make($this->route->dispatch(), 'global'))
            ->send();
    }

    /**
     * 这个不知道有没有必要
     */
    public function __destruct()
    {
        static::$instances = [];
    }
}
