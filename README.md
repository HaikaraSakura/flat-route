# FlatRoute

## セットアップ

```shell
composer require haikara/flat-route
```

ContainerInterfaceの実装が必要なので、任意のライブラリを用意する。    
league/containerを使用する。

```PHP
// ContainerInterfaceの実装を用意
$container = new League\Container();

// Routerクラスをインスタンス化
$router = new Haikara\FlatRoute\Router($container);

// サブディレクトリ運用の場合、ベースになるURIを設定する
$router->setBaseRoute('/sub-project');
```

## ルーティング設定

### 基本的なルーティング

PSR-7の実装が必要。下記の例ではlaminas/laminas-diactorosを利用する。

Router::get/Router::postの第一引数にパスを指定し、第二引数に実行したい処理を渡す。  
パスのことをルーティングパターン、実行したい処理のことをルーティングコールバックという。

```PHP
$response = new Laminas\Diactoros\Response;

$router->get('/', function ($request, $args) use ($response) {
    $response->getBody()->write('<h1>トップページ</h1>');
    return $response;
}),

$router->get('/products/create', function ($request, $args) use ($response) {
    $response->getBody()->write('<h1>製品登録画面</h1>');
    return $response;
}),

$router->post('/products/store', function ($request, $args) use ($response) {
    $response->getBody()->write('<h1>製品登録処理</h1>');
    return $response;
});

// RouterにRequestを渡して実行
$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals();
$response = $router->handle($request);

// Responseを出力
(new Haikara\FlatRoute\Emitter\ResponseEmitter($response))->payout();
```

## ルーティングコールバックとDIコンテナ

ルーティングコールバックにはクラスの完全修飾名を渡すこともできる。  
クラス名はcallable値ではないが、Containerによって自動的にインスタンス化される。

```PHP
$router->get('/', TopAction::class);
```

- `__invoke`を実装していること
- `__invoke`の引数が$request, $argsであること
- `__invoke`の返り値が`ResponseInterface`であること


インスタンス化してオブジェクトをコールバックとして渡すこともできるが、  
そうするとすべてのActionクラスが事前にインスタンス化されてしまうので効率が悪い。  
完全修飾名を渡す方式であれば、該当のルートのActionのみがインスタンス化される。

## パラメータのフィルタリングとバリデーション

ruleメソッドを用いて、ルーティングパラメータの値をフィルタリングし、  
特定の値のみ受け付けるよう制限を加えることが可能。やりすぎ注意。

```PHP
// year => 2000年以降
// month => 1月から12月
$router->get('/column/:year/:month', ColumnIndexAction::class)
    ->rule('year', Rules::integer()->min(2000))
    ->rule('month', Rules::integer()->range(1, 12));

// user_id => 英数字、10ケタ
$router->get('/profile/:user_id', UserProfileInexAction::class)
    ->rule('user_id', Rules::alnum()->length(10));

// customer_code => 数字のみ、8ケタ（0埋めありの文字列を想定）
$router->get('/customers/:customer_code/edit', CustomersEditAction::class)
    ->rule('customer_code', Rules::digit()->length(8));

// filename =>拡張子が 'jpg', 'png', 'webp'のいずれか
$router->get('/products/images/:filename', ProductsImageAction::class)
    ->rule('filename', Rules::file()->ext('jpg', 'png', 'webp'));
```

## ミドルウェア

ルーティングと各ルートにはMiddlewareを追加することができる。  
Middlewareのオブジェクトか完全修飾名を渡すこと。

- あとから追加したMiddlewareのほうが外側で実行される＝最後に追加したMiddlewareが一番はじめに実行される。
- ルーティングに追加されたMiddlewareは、全ルートに適用される。
- ルーティングに追加されたMiddlewareは、各ルートが持つMiddlewareより常に外側で実行される。
- ルーティングコールバックが一番内側で実行される。

```PHP

class Middleware1 implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        echo '1';
        $response = $handler->handle($request);
        echo '1';
        return $response;
    }
}

class Middleware2 implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        echo '2';
        $response = $handler->handle($request);
        echo '2';
        return $response;
    }
}

class Middleware3 implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        echo '3';
        $response = $handler->handle($request);
        echo '3';
        return $response;
    }
}

class Middleware4 implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        echo '4';
        $response = $handler->handle($request);
        echo '4';
        return $response;
    }
}

// ルートにMiddlewareを追加
$router->get('/main_action', function ($request, $args) use ($response) {
    echo ' Action ';
    return $response;
})
->addMiddleware(Middleware1::class)
->addMiddleware(Middleware2::class);

// ルーティング全体にMiddlewareを追加
$router->addMiddleware(Middleware3::class);
$router->addMiddleware(Middleware4::class);

// 出力される内容
// 4321 Action 1234 
```

## オプショナルパターン

ノードが増減するルーティングパターンに同じActionを設定する場合、  
下記のようにすべてのパターンを別個に設定することもできるが、  
特にバリデーションやMiddlewareの設定を伴う場合に、記述が冗長になりやすい。

```PHP
// 全記事
$router->get('/column', ColumnIndexAction::class)
    ->addMiddleware(Middleware1::class);

// 特定の年の記事
$router->get('/column/:year', ColumnIndexAction::class)
    ->rule('year', Rules::integer()->min(2000))
    ->addMiddleware(Middleware1::class);

// 特定の年月の記事
$router->get('/column/:year/:month', ColumnIndexAction::class)
    ->rule('year', Rules::integer()->min(2000))
    ->rule('month', Rules::integer()->range(1, 12))
    ->addMiddleware(Middleware1::class);

// こんなのやってられない！
```

あってもなくてもいい部分を[]で囲むと一括で設定することができる。

```PHP
$router->get('/column[/:year][/:month]', ColumnIndexAction::class)
    ->rule('year', Rules::integer()->min(2000))
    ->rule('month', Rules::integer()->range(1, 12))
    ->addMiddleware(Middleware1::class);
```

## ルートネームとパスの生成

ルートに名前を付けることで、そのルートのパスを別のActionで簡単に組み立てられるようになる。

```PHP
$router->get('/column[/:year][/:month]', ColumnIndexAction::class)
    ->rule('year', Rules::integer()->min(2000))
    ->rule('month', Rules::integer()->range(1, 12))
    ->setName('ColumnIndex'); // Route::setNameでルート名を設定

// 別のルートのActionにて、ルート名を指定して NamedRoutePatterns::getRoutePathを呼ぶと、
// パラメータが割り当てられたパスの文字列を取得できる。
$router->get('/products/:product_id', function ($request, $args) use ($response) {
    $path = NamedRoutePatterns::getRoutePath(
        $request,
        'ColumnIndex',
        ['year' => 2023, 'month' => 1]
    );
     // '/column/2023/1'が得られる
     
    return $response;
});
```

## ルートグループ

ルートをグループ化し、Middlewareを一括で登録することができる。

```PHP
// 通常のルーティング
$router->get('/admin/login', LoginAction::class);

// グループ化されたルーティング
$router
    ->group('/admin', function (RouteGroup $router) {
        $router->get('/products', ProductsIndexAction::class);
        $router->get('/customers', CustomersIndexAction::class);
    })
    ->addMiddleware(AuthMiddleware::class);
```

上記の例では`/admin/products`と`/admin/customers`に一括で`AuthMiddleware`が適用される。  
`/admin/login`も`/admin`配下のルートだが、グループには含まれていないので`AuthMiddleware`の適用外となる。

グループはネスト可能。

## リクエストメソッドによってActionを分ける

同じルートパターンで異なるリクエストメソッドに別々のActionをセットできる。  
通常のフォームではGETとPOSTしかないが、REST APIの設計で活用できる。

```PHP
// ユーザー一覧画面
$router->get('/users', UsersIndexAction::class);

// ユーザー登録画面
$router->get('/users/create', UsersCreateAction::class);

// ユーザー登録処理
$router->post('/users', UsersIndexAction::class);

// ユーザー情報編集画面
$router->get('/users/:id',  UsersEditAction::class);

// ユーザー情報編集画面
$router->patch('/users/:id',  UsersUpdateAction::class);

// ユーザー削除処理
$router->delete('/users/:id',  UsersUpdateAction::class);
```
