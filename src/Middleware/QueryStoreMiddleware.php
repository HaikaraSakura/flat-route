<?php

namespace Haikara\FlatRoute\Middleware;

use Haikara\FlatRoute\RouteProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 設定されたルートのクエリパラメータをセッションに保存するミドルウェア
 */
class QueryStoreMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeProvider  = RouteProvider::createFromRequest($request);
        $currentRouteName = $routeProvider->getCurrentRouteName();

        // クエリパラメータをセッションに保存
        $_SESSION[static::class][$currentRouteName] = $request->getQueryParams();

        return $handler->handle($request);
    }
}
