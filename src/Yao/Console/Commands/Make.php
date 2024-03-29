<?php
declare(strict_types=1);

namespace Yao\Console\Commands;

class Make
{

    private array $optionsMap = [
        1 => 'controller',
        2 => 'model',
        3 => 'middleware'
    ];

    public function out()
    {
        echo <<<EOT
(1). 控制器
(2). 模型
(3). 中间件
输入要生成的文件<1,2,3>：
EOT;
        fscanf(STDIN, '%d', $options);
        if (array_key_exists($options, $this->optionsMap)) {
            return call_user_func([$this, $this->optionsMap[$options]]);
        }
        return $this->out();
    }

    public function controller()
    {

        echo <<<EOT
(1). 普通控制器
(2). 资源控制器
选择控制器类型:
EOT;
        fscanf(STDIN, '%d', $type);
        if (1 == $type) {
            $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Commands' . DIRECTORY_SEPARATOR . 'make' . DIRECTORY_SEPARATOR . 'controller.tpl';
        } else if (2 == $type) {
            $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Commands' . DIRECTORY_SEPARATOR . 'make' . DIRECTORY_SEPARATOR . 'controller_rest.tpl';
        } else {
            return $this->controller();
        }
        echo '输入控制器：';
        fscanf(STDIN, '%s', $behavior);

        $array = explode('/', $behavior);

        $controller = ucfirst(array_pop($array));

        $namespace = implode('\\', array_map(function ($value) {
            return ucfirst($value);
        }, $array));

        $path = env('app_path') . 'Http' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $file = str_replace(['{{namespace}}', '{{class}}'], ['App\\Http\\Controllers\\' . $namespace, $controller], file_get_contents($file));
        file_put_contents($path . $controller . '.php', $file);
        exit('控制器App\\Http\\Controllers\\' . $namespace . '\\' . $controller . "创建成功！\n");
    }


    public function model()
    {
        exit("暂时不支持!\n");
    }

    public function middleware()
    {
        exit("暂时不支持!\n");
    }
}
