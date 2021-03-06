<?php

namespace Yao\Http\Middleware;

use Yao\App;
use Yao\Http\Route\Cors;

class AllowCors
{
    public function handle($request, \Closure $next, App $app)
    {
        $app[Cors::class]->allow();
        return $next($request);
    }

}