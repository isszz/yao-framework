<?php

namespace Yao;

class Container
{
    /**
     * 依赖注入的类实例
     * @var array
     */
    protected static array $instances = [];

    /**
     * 当前实例化并调用方法的类名
     * @var $instance
     */
    protected static $abstract;
    /**
     * 绑定的类名
     * @var array|string[]
     */
    protected static array $bind = [
        'request' => \Yao\Http\Request::class,
        'validate' => \App\Http\Validate::class,
        'file' => File::class,
        'env' => Env::class,
        'config' => Config::class,
        'app' => App::class,
        'view' => View::class,
        'route' => \Yao\Route::class
    ];

    /**
     * 获取绑定类名
     * @param $name
     * @return mixed|string
     */
    protected static function _getBindClass(string $name): string
    {
        return static::$bind[strtolower($name)] ?? $name;
    }

    /**
     * 获取类对象，支持依赖注入
     * @param $abstract
     * 需要实例化的类
     * @param array $arguments
     * 给构造方法传递的参数
     * @param false $singleInstance
     * 为true表示单例
     * @return mixed
     * @throws \ReflectionException
     */
    public static function make(string $abstract, array $arguments = [], bool $singleInstance = false)
    {
        $abstract = static::_getBindClass($abstract);
        if (!isset(static::$instances[$abstract]) || !$singleInstance) {
            $reflectionClass = new \ReflectionClass($abstract);
            if (null === ($constructor = $reflectionClass->getConstructor())) {
                static::$instances[$abstract] = new $abstract(...$arguments);
            } else if ($constructor->isPublic()) {
                $parameters = $constructor->getParameters();
                $injectClass = static::_getInjectObject($parameters);
                static::$instances[$abstract] = new $abstract(...[...$arguments, ...$injectClass]);
            }
        }
        return static::$instances[$abstract];
    }

    /**
     * 获取实例并返回容器类对象，可以直接调用实例中的方法
     * @param string $abstract
     * 需要实例化的类
     * @param array $arguments
     * 给构造方法传递的参数
     * @param bool $singleInstance
     * true表示单例
     * @return static
     * @throws \ReflectionException
     */
    public static function get(string $abstract, array $arguments = [], bool $singleInstance = false)
    {
        self::$abstract = $abstract;
        self::make($abstract, $arguments, $singleInstance);
        return new static();
    }

    /**
     * 调用类的方法
     * @param array $callable
     * 可调用的类和方法数组['className','methodName']
     * @param array $arguments
     * 给方法传递的参数
     * @param false $singleInstance
     * true表示单例
     * @param array $constructorParameters
     * 给构造方法传递的参数
     * @return mixed
     */
    public static function invokeMethod(array $callable, array $arguments = [], bool $singleInstance = false, array $constructorParameters = [])
    {
        [$class, $method] = [static::_getBindClass($callable[0]), $callable[1]];
        static::make($class, $constructorParameters, $singleInstance);
        $parameters = (new \ReflectionClass($class))->getMethod($method)->getParameters();
        $injectClass = static::_getInjectObject($parameters);
        return call_user_func_array([static::$instances[$class], $method], [...$arguments, ...$injectClass]);
    }


    /**
     * 通过参数列表获取注入对象数组
     * @param $parameters
     * @return array
     */
    protected static function _getInjectObject(array $parameters)
    {
        $injectClass = [];
        foreach ($parameters as $parameter) {
            if (!is_null($class = $parameter->getClass())) {
                $className = $class->getName();
                $injectClass[] = new $className();
            }
        }
        return $injectClass;
    }

    /**
     * 实例方法调用接口，本类中不应该出现任何除了构造方法以外的非静态方法
     * @param $method
     * 直接调用的方法名
     * @param array $arguments
     * 直接调用方法时给方法传递的参数
     * @return mixed
     */
    public function __call($method, $arguments = [])
    {
        return self::invokeMethod([self::$abstract, $method], $arguments);
    }
}
