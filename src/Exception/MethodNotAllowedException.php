<?php

namespace Haikara\FlatRoute\Exception;

use Exception;
use Throwable;

class MethodNotAllowedException extends Exception implements RoutingExceptionInterface
{
    public function __construct(
        string $message = '405 Method Not Allowed',
        int $code = 405,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
