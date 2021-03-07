<?php
declare(strict_types=1);

namespace Yao\Exception;


use Throwable;

class ErrorException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}