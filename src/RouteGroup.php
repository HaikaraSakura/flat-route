<?php

declare(strict_types=1);

namespace Haikara\FlatRoute;

use Generator;
use IteratorAggregate;
use Haikara\FlatRoute\Route\RouteCollection;
use Haikara\FlatRoute\Route\RouteInterface;

class RouteGroup implements RouterInterface, IteratorAggregate
{
    use RouterTrait;

    protected function __construct()
    {
        $this->routes = new RouteCollection();
    }

    public static function createWithBasePath(string $basePath): static {
        $group = new static;
        $group->setBasePath($basePath);
        return $group;
    }

    /**
     * @return Generator<RouteInterface>
     */
    public function getIterator(): Generator
    {
        foreach ($this->routes as $route) {
            $route->addMiddleware(...$this->middlewares);
            yield $route;
        }
    }
}
