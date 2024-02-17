<?php

declare(strict_types=1);

namespace Haikara\FlatRoute\Route;

use function join;
use function preg_match_all;
use function strlen;
use function substr;

class OptionalRoute implements RouteInterface {
    use RouteTrait;

    /**
     * @var RouteInterface[]
     */
    protected array $routes = [];

    public function __construct(string $pattern) {
        $this->pattern = $pattern;

        // オプショナルセグメントを抽出
        preg_match_all('/\[\/(.*?)]/', $pattern, $matches);
        $optionalSegments = $matches[1];

        $basePattern = substr($pattern, 0, -strlen(join($matches[0])));
        $this->routes[] = RouteFactory::create($basePattern);

        foreach ($optionalSegments as $optionalSegment) {
            $basePattern .= '/' . $optionalSegment;
            $this->routes[] = RouteFactory::create($basePattern);
        }
    }

    public function match(string $requestPath): bool {
        foreach ($this->routes as $route) {
            foreach ($this->rules as $name => $rule) {
                $route->rule($name, $rule);
            }

            if ($route->match($requestPath)) {
                $route->addMiddleware(...$this->middlewares);

                foreach ($this->actions as $method => $action) {
                    $route->addAction($method, $action);
                }

                $this->args = $route->getArgs();

                return true;
            }
        }

        return false;
    }
}
