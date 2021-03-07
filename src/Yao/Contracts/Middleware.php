<?php
declare(strict_types=1);

namespace Yao\Contracts;

interface Middleware
{
    public function handle($request, \Closure $next);
}