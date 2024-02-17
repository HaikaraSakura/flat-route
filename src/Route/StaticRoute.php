<?php

declare(strict_types=1);

namespace Haikara\FlatRoute\Route;

class StaticRoute implements RouteInterface {
    use RouteTrait;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public function match(string $requestPath): bool {
        return $this->pattern === $requestPath;
    }
}
