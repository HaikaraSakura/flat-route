<?php

declare(strict_types=1);

namespace Haikara\FlatRoute;

use Haikara\FlatRoute\Exception\RouteArgumentException;
use Haikara\FlatRoute\Middleware\QueryStoreMiddleware;
use Psr\Http\Message\ServerRequestInterface;

use function array_key_exists;
use function implode;
use function ltrim;
use function preg_match_all;
use function str_contains;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;

class RouteProvider
{
    protected ServerRequestInterface $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public static function createFromRequest(ServerRequestInterface $request): RouteProvider {
        return new static($request);
    }

    public function has(string $routeName): bool {
        return isset($this->routes[$routeName]);
    }

    public function getArguments(): array {
        return $this->request->getAttribute('routingArguments');
    }

    public function getCurrentRouteName(): ?string {
        return $this->request->getAttribute('currentRouteName');
    }

    public function getQueryParamsByRouteName(string $routeName): array {
        return $_SESSION[QueryStoreMiddleware::class][$routeName] ?? [];
    }

    /**
     * @throws RouteArgumentException
     */
    public function getPath(string $routeName, array $args = []): ?string
    {
        $routePatterns = $this->request->getAttribute('namedRoutePatterns');

        $routePattern = null;

        foreach ($routePatterns as $name => $pattern) {
            if ($name === $routeName) {
                $routePattern = $pattern;
                break;
            }
        }

        if ($routePattern === null) {
            return null;
        }

        // オプショナルルートか
        if (static::isOptional($routePattern)) {
            preg_match_all('/\[\/(.*?)]/', $routePattern, $matches);
            $basePattern = substr($routePattern, 0, -strlen(implode($matches[0])));
            $optionalSegments = $matches[1];
        } else {
            $basePattern = $routePattern;
            $optionalSegments = [];
        }

        $basePatternSegments = explode('/', trim($basePattern, '/'));
        $pathSegments = [];

        // 必須部分
        foreach ($basePatternSegments as $basePatternSegment) {
            if (static::isPlaceholder($basePatternSegment)) {
                $name = ltrim($basePatternSegment, ':');

                if (!array_key_exists($name, $args)) {
                    throw new RouteArgumentException("{$name}が指定されていません。必須項目です。");
                }

                $pathSegment = $args[$name];
            } else {
                $pathSegment = $basePatternSegment;
            }

            $pathSegments[] = $pathSegment;
        }

        // オプショナル部分
        foreach ($optionalSegments as $optionalSegment) {
            if (!static::isPlaceholder($optionalSegment)) {
                $pathSegments[] = $optionalSegment;
                continue;
            }

            $name = ltrim($optionalSegment, ':');

            if (!array_key_exists($name, $args)) {
                // パスの組み立てを終了
                break;
            }

            $pathSegments[] = $args[$name];
        }


        return '/' . implode('/', $pathSegments);
    }

    protected static function isPlaceholder(string $segment): bool
    {
        return str_starts_with($segment, ':');
    }

    protected static function isOptional(string $routePattern): bool
    {
        return str_contains($routePattern, '[') && str_contains($routePattern, ']');
    }
}
