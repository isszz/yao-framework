<?php

namespace Yao\Http\Middleware;

use Yao\App;
use Yao\Contracts\Middleware;
use Yao\Http\Route\Cors;

class AllowCors implements Middleware
{
    public function handle($request, \Closure $next)
    {
        App::instance()[Cors::class]->allow();
        return $next($request);
    }

}