<?php

namespace Haikara\FlatRoute\Exception;

use Exception;
use Throwable;

class NotFoundException extends Exception implements RoutingExceptionInterface
{
    public function __construct(
        string $message = '404 Not Found',
        int $code = 404,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
