<?php

declare(strict_types=1);

namespace Haikara\FlatRoute\Route;

use function str_contains;

class RouteFactory
{
    public static function create(string $pattern): RouteInterface
    {
        return match (true) {
            static::isOptional($pattern) => new OptionalRoute($pattern),
            static::isDynamic($pattern) => new DynamicRoute($pattern),
            default => new StaticRoute($pattern)
        };
    }

    /**
     * オプショナルなセグメントを含むかどうか
     * @param string $pattern
     * @return bool
     */
    protected static function isOptional(string $pattern): bool {
        return str_contains($pattern, '[') && str_contains($pattern, ']');
    }

    /**
     * プレースホルダーを含むかどうか
     * @param string $pattern
     * @return bool
     */
    protected static function isDynamic(string $pattern): bool {
        return str_contains($pattern, ':');
    }
}
