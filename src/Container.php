<?php

namespace Yao;


class Container
{

    const BINDCLASS = [
        'Request' => \Yao\Http\Request::class,
        'Validate' => \App\Http\Validate::class,
        'File' => File::class,
        'Env' => Env::class,
        'Config' => Config::class,
        'App' => App::class,
        'View' => View::class
    ];

    private function _getClass($class)
    {
        if (!is_string($class)) {
            $class = $class->getType()->getName();
        }
        if (!class_exists($class)) {
            $class = ltrim(strrchr($class, '\\'), '\\');
            if (!isset(self::BINDCLASS[$class])) {
                throw new \Exception('类' . $class . '不存在');
            } else {
                $class = self::BINDCLASS[$class];
            }
        }
        return $class;
    }

    public function get($class)
    {
        $reflectionClass = new \ReflectionClass($this->_getClass($class));
        return $reflectionClass;
    }

    public function getParams($class, $method)
    {
        $params = [];
        foreach ($this->get($class)->getMethod($method)->getParameters() as $param) {
            $params[] = $param->getType();
        }
        return $params;
    }


    public function inject($class, $inject, $params, $method)
    {
        foreach ($inject as $j) {
            $injectClass = $this->_getClass($j);
            $params[] = new $injectClass;
        }
        return (new $class())->$method(...$params);
    }

    public function create($class, $method, $params)
    {
        $methodParams = $this->get($class)->getMethod($method)->getParameters();
        $inject = array_diff_key($methodParams, $params);
        return $this->inject($class, $inject, $params, $method);
    }

}