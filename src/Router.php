<?php

declare(strict_types=1);

namespace Haikara\FlatRoute;

use Haikara\FlatRoute\Exception\MethodNotAllowedException;
use Haikara\FlatRoute\Exception\NotFoundException;
use Haikara\FlatRoute\Exception\RoutingExceptionInterface;
use Haikara\FlatRoute\Middleware\QueryStoreMiddleware;
use Haikara\FlatRoute\Route\RouteInterface;
use Haikara\MiddlewareStack\RequestHandler;
use Haikara\MiddlewareStack\RequestHandlerFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Haikara\FlatRoute\Route\RouteCollection;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function class_exists;
use function is_callable;
use function is_string;

class Router implements RouterInterface, RequestHandlerInterface
{
    use RouterTrait;

    protected ?ContainerInterface $container = null;

    public function __construct()
    {
        $this->routes = new RouteCollection();
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Requestに合致するルートのコールバックを実行し、Responseを返す
     * Containerがセットされていれば依存解決をおこなう
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws RoutingExceptionInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route = $this->routes->getFromRequestPath($request);

        if ($route === null) {
            throw new NotFoundException();
        }

        if ($route->hasMethod($request) === false) {
            throw new MethodNotAllowedException();
        }

        $handler = $this->createRequestHandler();

        // ルーティングコールバックをRequestHandlerに追加
        $handler->addMiddleware($this->createAction($request, $route));

        // ルートに登録されたMiddlewareをRequestHandlerに追加
        $handler->addMiddlewares($route->getMiddlewares());

        // ルーターに登録されたMiddlewareをRequestHandlerに追加
        $handler->addMiddlewares($this->middlewares);

        $request = $request
            ->withAttribute('currentRouteName', $route->getName())
            ->withAttribute('routingArguments', $route->getArgs())
            ->withAttribute('namedRoutePatterns', $this->routes->getNamedRoutePatterns())
            ->withAttribute('queryStorage', $_SESSION[QueryStoreMiddleware::class] ?? []);

        unset($this->routes);

        return $handler->handle($request);
    }

    /**
     * RequestHandlerを作成する
     * $this->containerの有無で切り替え
     * @return RequestHandler
     */
    protected function createRequestHandler(): RequestHandlerInterface
    {
        return $this->container instanceof ContainerInterface
            ? (new RequestHandlerFactory())->createFromContainer($this->container)
            : RequestHandlerFactory::create();
    }

    /**
     * Routeからルーティングコールバックを取得し、Middlewareに変換する
     *
     * @param ServerRequestInterface $request
     * @param RouteInterface $route
     * @return MiddlewareInterface|RequestHandlerInterface
     * @throws MethodNotAllowedException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createAction(ServerRequestInterface $request, RouteInterface $route): MiddlewareInterface|RequestHandlerInterface
    {
        $routingCallback = $route->getAction($request);

        if (is_string($routingCallback) && class_exists($routingCallback) && isset($this->container)) {
            $routingCallback = $this->container->get($routingCallback);
        }

        return is_callable($routingCallback)
            ? new ActionProcessor($routingCallback)
            : $routingCallback;
    }
}
