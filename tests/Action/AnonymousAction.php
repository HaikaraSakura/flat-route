<?php

declare(strict_types=1);

namespace Haikara\FlatRoute\Test\Action;

use Haikara\FlatRoute\RouteProvider;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AnonymousAction
{
    public function __invoke(ServerRequestInterface $request, $args): ResponseInterface
    {
        $routeProvider = RouteProvider::createFromRequest($request);
        $columnListPath = $routeProvider->getPath('ColumnList', ['year' => 2021, 'month' => 1, 'day' => 1]);
        $productListQueryParams = $routeProvider->getQueryParamsByRouteName('ProductList');

        var_dump($columnListPath);
        var_dump($args);
        var_dump($productListQueryParams);

        $response = new Response();
        $response->getBody()->write($request->getUri()->getPath());

        return $response;
    }
}
