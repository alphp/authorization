# 認可ミドルウェア

Authorization はミドルウェアとしてアプリケーションに適用されます。
`AuthorizationMiddleware` は次の役割を持ちます。

- request の `identity` に `can`、`canResult`、`applyScope` を追加する
- authorization が実行または明示的にスキップされたことを確認する

```php
namespace App;

use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Policy\OrmResolver;
use Cake\Http\BaseApplication;
use Psr\Http\Message\ServerRequestInterface;

class Application extends BaseApplication implements AuthorizationServiceProviderInterface
{
    public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
    {
        $resolver = new OrmResolver();

        return new AuthorizationService($resolver);
    }

    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue->add(new AuthorizationMiddleware($this));

        return $middlewareQueue;
    }
}
```

## Identity Decorator

デフォルトでは request の `identity` は
`Authorization\IdentityDecorator` でラップされます。

```php
$originalUser = $user->getOriginalData();
```

### User クラスを identity として使う

```php
$middlewareQueue->add(new AuthorizationMiddleware($this, [
    'identityDecorator' => function ($auth, $user) {
        return $user->setAuthorization($auth);
    }
]));
```

## 認可を確実に適用する

```php
$middlewareQueue->add(new AuthorizationMiddleware($this, [
    'requireAuthorizationCheck' => false
]));
```

## 不正なリクエストへの対処

組み込みハンドラー:

- `Exception`
- `Redirect`
- `CakeRedirect`

```php
use Authorization\Exception\MissingIdentityException;

$middlewareQueue->add(new AuthorizationMiddleware($this, [
    'unauthorizedHandler' => [
        'className' => 'Authorization.Redirect',
        'url' => '/pages/unauthorized',
        'queryParam' => 'redirectUrl',
        'exceptions' => [
            MissingIdentityException::class,
            OtherException::class,
        ],
    ],
]));
```

必要であれば `RedirectHandler` を継承して独自ハンドラーを作成できます。
