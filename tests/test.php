<?php

declare(strict_types=1);

use Haikara\FlatRoute\Middleware\QueryStoreMiddleware;
use Haikara\FlatRoute\RouteGroup;
use Haikara\FlatRoute\Router;
use Haikara\FlatRoute\Test\Action\AnonymousAction;
use Haikara\FlatRoute\Test\Middleware\Middleware1;
use Haikara\FlatRoute\Test\Middleware\Middleware2;
use Haikara\FlatRoute\Test\Middleware\Middleware3;
use Haikara\Verifier\Rules;
use Laminas\Diactoros\ServerRequestFactory;
use League\Container\Container;
use League\Container\ReflectionContainer;

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

$start = microtime(true);

print "実行開始[メモリ使用量]：" . memory_get_usage() / (1024 * 1024) . "MB\n";
print "実行開始[メモリ最大使用量]：" . memory_get_peak_usage() / (1024 * 1024) . "MB\n";

$container = (new Container())
    ->delegate(new ReflectionContainer());

$container->add(DateTimeInterface::class, fn () => new DateTimeImmutable());

$router = new Router();
$router->setBasePath('/sub');
$router->setContainer($container);

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/sub/column/2023/12/31';
$_SESSION[QueryStoreMiddleware::class]['ProductList'] = ['page' => 1, 'product_name' => '商品1'];

$router->addMiddleware(Middleware3::class);

$router
    ->get('/', AnonymousAction::class)
    ->addMiddleware(Middleware1::class, Middleware2::class)
    ->setName('Top');

$router->group('/admin', function (RouteGroup $router) {
    $router
        ->get('/product', AnonymousAction::class)
        ->setName('AdProductList');

    $router->group('/account', function (RouteGroup $router) {
        $router->addMiddleware(Middleware2::class);

        $router
            ->get('/create', AnonymousAction::class)
            ->addMiddleware(Middleware1::class);
        $router->post('/store', AnonymousAction::class);
        $router->get('/:id', AnonymousAction::class);
        $router->post('/:id/update', AnonymousAction::class);
        $router->post('/:id/delete', AnonymousAction::class);
    });
});

$router
    ->get('/product', AnonymousAction::class)
    ->addMiddleware(QueryStoreMiddleware::class)
    ->setName('ProductList');

$router
    ->get('/column[/:year][/:month][/:day]', AnonymousAction::class)
    ->rule('year', Rules::integer())
    ->rule('month', Rules::integer()->range(1, 12))
    ->rule('day', Rules::integer()->range(1, 31))
    ->setName('ColumnList');

for ($i = 0; $i < 30; $i++) {
    $router->get("/{$i}", AnonymousAction::class)
        ->addMiddleware(Middleware1::class, Middleware2::class)
        ->setName("/{$i}");

    $router->get("/{$i}/products", AnonymousAction::class)
        ->addMiddleware(Middleware1::class, Middleware2::class)
        ->setName("/{$i}/products");

    $router->get("/{$i}/products/create", AnonymousAction::class)
        ->addMiddleware(Middleware1::class, Middleware2::class)
        ->setName("/{$i}/products/create");

    $router->post("/{$i}/products/store", AnonymousAction::class)
        ->addMiddleware(Middleware1::class, Middleware2::class)
        ->setName("/{$i}/products/store");

    $router->get("/{$i}/products/:id", AnonymousAction::class)
        ->rule('id', Rules::integer()->min(1))
        ->addMiddleware(Middleware1::class, Middleware2::class)
        ->setName("/{$i}/products/:id");

    $router->post("/{$i}/products/:id/update", AnonymousAction::class)
        ->rule('id', Rules::integer()->min(1))
        ->addMiddleware(Middleware1::class, Middleware2::class)
        ->setName("/{$i}/products/:id/update");

    $router->post("/{$i}/products/:id/delete", AnonymousAction::class)
        ->rule('id', Rules::integer()->min(1))
        ->addMiddleware(Middleware1::class, Middleware2::class)
        ->setName("/{$i}/products/:id/delete");

    $router->get("/{$i}/column[/:year][/:month][/:day]", AnonymousAction::class)
        ->rule('year', Rules::integer())
        ->rule('month', Rules::integer()->range(1, 12))
        ->rule('day', Rules::integer()->range(1, 31))
        ->addMiddleware(Middleware1::class, Middleware2::class)
        ->setName("/{$i}/column[/:year][/:month][/:day]");
}

$request = ServerRequestFactory::fromGlobals(server: $_SERVER);

$response = $router->handle($request);

echo $response->getBody() . PHP_EOL;

echo (microtime(true) - $start) . PHP_EOL;

print "処理1実行後[メモリ使用量]：" . memory_get_usage() / (1024 * 1024) . "MB\n";
print "処理1実行後[メモリ最大使用量]：" . memory_get_peak_usage() / (1024 * 1024) . "MB\n";
