<?php
declare(strict_types=1);

namespace Yao\View\Engines;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Yao\View\Engine;

class Twig extends Engine
{

    /**
     * Twig实例
     * @var Environment
     */
    private Environment $twig;

    public function init()
    {
        $loader = new FilesystemLoader(env('views_path'));
        $this->twig = new Environment($loader, [
            'debug' => $this->config['debug'],
            'cache' => $this->config['cache'] ? env('cache_path') . 'view' : false,
        ]);
    }

    public function render($arguments = [])
    {
        return $this->twig->render($this->template, $arguments);
    }
}
