<?php
declare(strict_types=1);

namespace Yao;

use Psr\Container\ContainerInterface;
use Yao\Exception\ContainerException;
use Yao\Concerns\SingleInstance;

/**
 * 一个简单的容器类
 * Class Container
 * @package Yao
 */
class Container implements ContainerInterface, \ArrayAccess
{

    /**
     * 单例模式
     */
    use SingleInstance;

    /**
     * 依赖注入的类实例
     * @var array
     */
    protected static array $instances = [];

    /**
     * 绑定的类名
     * @var array|string[]
     */
    protected array $bind = [];

    /**
     * 单例模式获取类实例
     * 从static::$instances中实例，和依赖注入获取相同实例
     * @return static
     */
    public static function instance()
    {
        if (!isset(static::$instances[static::class]) || !static::$instances[static::class] instanceof static) {
            static::$instances[static::class] = new static();
        }
        return static::$instances[static::class];
    }

    /**
     * 将实例化的类存放到数组中
     * @param string $abstract
     * 类名
     * @param object $instance
     * 实例话后的对象
     */
    public function set(string $abstract, object $instance): void
    {
        static::$instances[$this->_getBindClass($abstract)] = $instance;
    }

    /**
     * 获取存在的实例
     * @param string $id
     * 类的标识[完整类名]
     * @return mixed
     */
    public function get($id)
    {
        $abstract = $this->_getBindClass($id);
        if ($this->has($abstract)) {
            return static::$instances[$abstract];
        }
        throw new ContainerException('No instance found: ' . $abstract);
    }

    /**
     * 判断类的实例是否存在
     * @param string $id
     * 类的标识[完整类名]
     * @return bool
     */
    public function has($id)
    {
        return isset(static::$instances[$this->_getBindClass($id)]);
    }

    /**
     * 添加绑定类的标识
     * @param string $id
     * 绑定的类标识
     * @param string $className
     * 绑定的类名
     */
    public function bind(string $id, string $className): void
    {
        $this->bind[$id] = $className;
    }


    /**
     * 获取绑定类名
     * @param $name
     * @return mixed|string
     */
    protected function _getBindClass(string $name): string
    {
        return $this->bind[strtolower($name)] ?? $name;
    }

    /**
     * 注入的外部接口方法
     * @param string $abstract
     * 需要实例化的类名
     * @param array $arguments
     * 索引数组的参数列表
     * @param bool $singleInstance
     * 是否单例，true为单例，false为非单例
     * @return mixed
     */
    public function make(string $abstract, array $arguments = [], bool $singleInstance = true): object
    {
        $abstract = $this->_getBindClass($abstract);
        //将参数处理成索引数组
        $arguments = array_values($arguments);
        if (!$singleInstance) {
            //非单例会强制刷新当前存在的单例实例
            $this->remove($abstract);
            //返回依赖注入后的实例
            return $this->_inject($abstract, $arguments);
        }
        if (!$this->has($abstract)) {
            $this->set($abstract, $this->_inject($abstract, $arguments));
        }
        return $this->get($abstract);
    }

    /**
     * 注销实例
     * @param $abstract
     */
    public function remove(string $abstract): bool
    {
        $abstract = $this->_getBindClass($abstract);
        if ($this->has($abstract)) {
            unset(self::$instances[$abstract]);
            return true;
        }
        return false;
    }

    /**
     * @param string $abstract
     * @param array $arguments
     * @return object
     * @throws \ReflectionException
     */
    /**
     * @param string $abstract
     * @param array $arguments
     * @return object
     * @throws \ReflectionException
     */
    private function _inject(string $abstract, array $arguments): object
    {
        $reflectionClass = new \ReflectionClass($abstract);
        //构造方法不存在直接实例化
        if (null === ($constructor = $reflectionClass->getConstructor())) {
            return new $abstract(...$arguments);
        }
        //构造方法为public
        if ($constructor->isPublic()) {
            //通过构造方法的参数列表注入实例
            $injectClass = $this->_injectArguments($constructor->getParameters(), $arguments);
            return new $abstract(...$injectClass);
        }
        throw new ContainerException('Cannot initialize class: ' . $abstract);
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
    public function invokeMethod(array $callable, $arguments = [], bool $singleInstance = true, $constructorParameters = [])
    {
        //取得完整类名和方法
        [$abstract, $method] = [$this->_getBindClass($callable[0]), $callable[1]];
        //获取容器中的实例
        $instance = $this->make($abstract, (array)$constructorParameters, $singleInstance);
        //获取要调用的参数列表
        $parameters = (new \ReflectionClass($abstract))->getMethod($method)->getParameters();
        //注入参数
        $injectClass = $this->_injectArguments($parameters, (array)$arguments);
        //调用方法
        return $instance->$method(...$injectClass);
    }

    /**
     * 构造注入参数列表
     * @param array $parameters
     * 方法的参数列表
     * @param array $arguments
     * 给方法传递的参数
     * @return array
     * @throws \Exception
     */
    protected function _injectArguments(array $parameters, array $arguments): array
    {
        //[DEBUG]所有注入的类都成了单例的了
        $injectClass = [];
        foreach ($parameters as $parameter) {
            //如果是一个类,这里可能需要对闭包进行注入
            if (!is_null($class = $parameter->getClass()) && 'Closure' !== $class->getName()) {
                //使用容器实例化该类并存放到reject中
                $injectClass[] = $this->make($class->getName(), [], true);
            } else if (!empty($arguments)) {
                //按顺序将非实例的参数存放到参数列表
                $injectClass[] = array_shift($arguments);
            }
        }
        return $injectClass;
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($abstract)
    {
        return $this->make($abstract);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->get($offset);
    }

    public function __get($abstract)
    {
        return $this->get($abstract);
    }

}
