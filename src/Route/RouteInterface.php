<?php

declare(strict_types=1);

namespace Haikara\FlatRoute\Route;

use Haikara\FlatRoute\Exception\MethodNotAllowedException;
use Haikara\Verifier\RuleInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

interface RouteInterface {
    /**
     * @param string $method
     * @param callable|class-string $callback
     * @return void
     */
    public function addAction(string $method, callable|string $callback): void;

    /**
     * @param MiddlewareInterface|class-string<MiddlewareInterface> ...$middlewares
     * @return $this
     */
    public function addMiddleware(MiddlewareInterface|string ...$middlewares): static;

    /**
     * @return array<MiddlewareInterface|class-string<MiddlewareInterface>>
     */
    public function getMiddlewares(): array;

    /**
     * @param string $placeholder
     * @param RuleInterface $rules
     * @return $this
     */
    public function rule(string $placeholder, RuleInterface $rules): static;

    /**
     * @return string
     */
    public function getPattern(): string;

    /**
     * @param string $routeName
     * @return $this
     */
    public function setName(string $routeName): static;

    /**
     * @return bool
     */
    public function hasName(): bool;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return array
     */
    public function getArgs(): array;

    /**
     * リクエストメソッドに紐づくルーティングコールバックが登録されているかどうか
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function hasMethod(ServerRequestInterface $request): bool;

    /**
     * リクエストメソッドに紐づくルーティングコールバックを取得
     * @param ServerRequestInterface $request
     * @return callable|class-string
     * @throws MethodNotAllowedException
     */
    public function getAction(ServerRequestInterface $request): callable|string;

    /**
     * @param string $requestPath
     * @return bool
     */
    public function match(string $requestPath): bool;
}
