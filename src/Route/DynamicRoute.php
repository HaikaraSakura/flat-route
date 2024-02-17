<?php

declare(strict_types=1);

namespace Haikara\FlatRoute\Route;

use Haikara\Verifier\Rules;

use function count;
use function explode;
use function ltrim;
use function str_starts_with;

class DynamicRoute implements RouteInterface
{
    use RouteTrait;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public function match(string $requestPath): bool
    {
        $requestPath = ltrim($requestPath, '/');

        // ルーティングパターンを分解
        // []を除去し、オプショナルセグメントも含める
        $patternSegments = explode('/', ltrim($this->pattern, '/'));

        // リクエストパスを分解
        $requestPathSegments = explode('/', $requestPath);

        // セグメントの数が合わなければマッチしない
        if (count($patternSegments) !== count($requestPathSegments)) {
            return false;
        }

        foreach ($patternSegments as $index => $patternSegment) {
            $pathSegment = $requestPathSegments[$index] ?? null;

            if ($pathSegment === null) {
                return false;
            }

            if (str_starts_with($patternSegment, ':')) {
                $ruleName = ltrim($patternSegment, ':');
                $rule = $this->rules[$ruleName] ?? Rules::string();

                if ($rule->match($pathSegment)) {
                    $this->args[$ruleName] = $rule->filter($pathSegment);
                    continue;
                }

                return false;
            }

            if ($patternSegment === $pathSegment) {
                continue;
            }

            return false;
        }

        return true;
    }
}
