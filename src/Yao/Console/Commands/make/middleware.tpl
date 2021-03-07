<?php

namespace {{namespace}};

class {{class}}
{

    public function handle($request, \Closure $next)
    {
        return $next($request);
    }

}
