<?php

declare(strict_types=1);

namespace Haikara\FlatRoute;

use Haikara\FlatRoute\Route\RouteInterface;
use Psr\Http\Server\MiddlewareInterface;

interface RouterInterface
{
    public const GET = 'GET';
    public const POST = 'POST';
    public const OPTIONS = 'OPTIONS';
    public const HEAD = 'HEAD';
    public const PUT = 'PUT';
    public const PATCH = 'PATCH';
    public const DELETE = 'DELETE';

    public const METHODS = [
        self::GET,
        self::POST,
        self::OPTIONS,
        self::HEAD,
        self::PUT,
        self::PATCH,
        self::DELETE
    ];

    /**
     * @param string $basePath
     * @return void
     */
    public function setBasePath(string $basePath): void;

    /**
     * GETメソッドのみのルーティング設定を追加する
     * @param string $pattern
     * @param callable|class-string $callback
     * @return RouteInterface
     */
    public function get(string $pattern, callable|string $callback): RouteInterface ;

    /**
     * POSTメソッドのみのルーティング設定を追加する
     * @param string $pattern
     * @param callable|class-string $callback
     * @return RouteInterface
     */
    public function post(string $pattern, callable|string $callback): RouteInterface;

    /**
     * OPTIONSメソッドのみのルーティング設定を追加する
     * @param string $pattern
     * @param callable|class-string $callback
     * @return RouteInterface
     */
    public function options(string $pattern, callable|string $callback): RouteInterface;

    /**
     * HEADメソッドのみのルーティング設定を追加する
     * @param string $pattern
     * @param callable|class-string $callback
     * @return RouteInterface
     */
    public function head(string $pattern, callable|string $callback): RouteInterface;

    /**
     * PUTメソッドのみのルーティング設定を追加する
     * @param string $pattern
     * @param callable|class-string $callback
     * @return RouteInterface
     */
    public function put(string $pattern, callable|string $callback): RouteInterface;

    /**
     * PATCHメソッドのみのルーティング設定を追加する
     * @param string $pattern
     * @param callable|class-string $callback
     * @return RouteInterface
     */
    public function patch(string $pattern, callable|string $callback): RouteInterface;

    /**
     * DELETEメソッドのみのルーティング設定を追加する
     * @param string $pattern
     * @param callable|class-string $callback
     * @return RouteInterface
     */
    public function delete(string $pattern, callable|string $callback): RouteInterface;

    /**
     * ANYメソッドのみのルーティング設定を追加する
     * @param string $pattern
     * @param callable|class-string $callback
     * @return RouteInterface
     */
    public function any(string $pattern, callable|string $callback): RouteInterface;

    /**
     * 許可したいメソッドを配列で指定してルーティング設定を追加する
     * @param self::METHODS $methods
     * @param string $pattern
     * @param callable|class-string $callback
     * @return RouteInterface
     */
    public function map(array $methods, string $pattern, callable|string $callback): RouteInterface;

    /**
     * @param MiddlewareInterface|class-string<MiddlewareInterface>|callable ...$middlewares
     * @return static
     */
    public function addMiddleware(...$middlewares): static;

    /**
     * ルーティング設定をグループ化する
     * $callbackの第一引数にRouteMapInterfaceが渡される
     * @param string $basePath
     * @param callable $callback
     * @return void
     */
    public function group(string $basePath, callable $callback): void;
}
