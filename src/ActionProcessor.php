<?php

declare(strict_types=1);

namespace Haikara\FlatRoute;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ActionProcessor implements MiddlewareInterface
{
    protected Closure $routingCallback;

    public function __construct(callable $routingCallback) {
        $this->routingCallback = Closure::fromCallable($routingCallback);
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $args = $request->getAttribute('routingArguments');
        return ($this->routingCallback)($request, $args);
    }
}
