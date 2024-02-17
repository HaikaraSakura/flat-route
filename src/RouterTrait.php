<?php

declare(strict_types=1);

namespace Haikara\FlatRoute;

use Haikara\FlatRoute\Route\RouteCollection;
use Haikara\FlatRoute\Route\RouteFactory;
use Haikara\FlatRoute\Route\RouteInterface;
use Psr\Http\Server\MiddlewareInterface;

use function ltrim;
use function rtrim;

trait RouterTrait
{
    /**
     * @var string
     */
    protected string $basePath = '/';

    /**
     * @var RouteCollection
     */
    protected RouteCollection $routes;

    /**
     * @var array<MiddlewareInterface|class-string<MiddlewareInterface>|callable>
     */
    protected array $middlewares = [];

    public function setBasePath(string $basePath): void
    {
        $this->basePath = $basePath;
    }

    /**
     * @inheritDoc
     */
    public function get(string $pattern, callable|string $callback): RouteInterface {
        return $this->map([RouterInterface::GET], $pattern, $callback);
    }

    /**
     * @inheritDoc
     */
    public function post(string $pattern, callable|string $callback): RouteInterface {
        return $this->map([RouterInterface::POST], $pattern, $callback);
    }

    /**
     * @inheritDoc
     */
    public function options(string $pattern, callable|string $callback): RouteInterface {
        return $this->map([RouterInterface::OPTIONS], $pattern, $callback);
    }

    /**
     * @inheritDoc
     */
    public function head(string $pattern, callable|string $callback): RouteInterface {
        return $this->map([RouterInterface::HEAD], $pattern, $callback);
    }

    /**
     * @inheritDoc
     */
    public function put(string $pattern, callable|string $callback): RouteInterface {
        return $this->map([RouterInterface::PUT], $pattern, $callback);
    }

    /**
     * @inheritDoc
     */
    public function patch(string $pattern, callable|string $callback): RouteInterface {
        return $this->map([RouterInterface::PATCH], $pattern, $callback);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $pattern, callable|string $callback): RouteInterface {
        return $this->map([RouterInterface::DELETE], $pattern, $callback);
    }

    /**
     * @inheritDoc
     */
    public function any(string $pattern, callable|string $callback): RouteInterface {
        return $this->map(RouterInterface::METHODS, $pattern, $callback);
    }

    /**
     * @inheritDoc
     */
    public function map(array $methods, string $pattern, callable|string $callback): RouteInterface
    {
        $pattern = rtrim(rtrim($this->basePath, '/') . '/' . ltrim($pattern, '/'), '/');

        if ($this->routes->has($pattern)) {
            $route = $this->routes->get($pattern);
        } else {
            $route = RouteFactory::create($pattern);
            $this->routes->add($route);
        }

        foreach ($methods as $method) {
            $route->addAction($method, $callback);
        }

        return $route;
    }

    /**
     * @param MiddlewareInterface|class-string<MiddlewareInterface>|callable ...$middlewares
     * @return static
     */
    public function addMiddleware(...$middlewares): static
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    public function group(string $basePath, callable $callback): void
    {
        $basePath = rtrim(rtrim($this->basePath, '/') . '/' . ltrim($basePath, '/'), '/');
        $group = RouteGroup::createWithBasePath($basePath);

        $callback($group);

        foreach ($group as $route) {
            $this->routes->add($route);
        }
    }
}
