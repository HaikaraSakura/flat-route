<?php

declare(strict_types=1);

namespace Haikara\FlatRoute\Test\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

class Middleware2 implements MiddlewareInterface
{
    public function process($request, $handler): ResponseInterface
    {
        echo __CLASS__ . PHP_EOL;
        return $handler->handle($request);
    }
};
