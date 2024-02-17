<?php

declare(strict_types=1);

namespace Haikara\FlatRoute\Route;

use Generator;
use IteratorAggregate;
use Psr\Http\Message\ServerRequestInterface;

use function rtrim;

class RouteCollection implements IteratorAggregate
{
    /**
     * @var array<string,StaticRoute>
     */
    protected array $staticRoutes = [];

    /**
     * @var array<string,DynamicRoute|OptionalRoute>
     */
    protected array $dynamicRoutes = [];

    public function add(RouteInterface $route): void
    {
        $pattern = $route->getPattern();

        if ($route instanceof StaticRoute) {
            $this->staticRoutes[$pattern] = $route;
        } else {
            $this->dynamicRoutes[$pattern] = $route;
        }
    }

    public function has(string $pattern): bool
    {
        return
            isset($this->staticRoutes[$pattern])
            || isset($this->dynamicRoutes[$pattern]);
    }

    public function get(string $pattern): RouteInterface
    {
        return
            $this->staticRoutes[$pattern]
            ?? $this->dynamicRoutes[$pattern];
    }

    public function getFromRequestPath(ServerRequestInterface $request): ?RouteInterface
    {
        $uri = $request->getUri();
        $requestPath = rtrim($uri->getPath(), '/');

        if (isset($this->staticRoutes[$requestPath])) {
            return $this->staticRoutes[$requestPath];
        }

        foreach ($this->dynamicRoutes as $route) {
            if ($route->match($requestPath)) {
                return $route;
            }
        }

        return null;
    }

    public function getNamedRoutePatterns(): Generator
    {
        foreach ($this->staticRoutes as $route) {
            if ($route->hasName()) {
                yield $route->getName() => $route->getPattern();
            }
        }

        foreach ($this->dynamicRoutes as $route) {
            if ($route->hasName()) {
                yield $route->getName() => $route->getPattern();
            }
        }
    }

    /**
     * @return Generator<RouteInterface>
     */
    public function getIterator(): Generator
    {
        yield from $this->staticRoutes;
        yield from $this->dynamicRoutes;
    }
}
