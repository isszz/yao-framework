<?php

namespace Yao\Contracts;

interface Middleware
{
    public function handle($request, \Closure $next);
}