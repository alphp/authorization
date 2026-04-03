# リクエスト認証ミドルウェア

このミドルウェアは、controller や action 単位で request 自体を認可したい
場合に使います。

`AuthorizationMiddleware` の後に追加してください。

## 使用方法

`src/Policy/RequestPolicy.php` を作成します。

```php
namespace App\Policy;

use Authorization\Policy\RequestPolicyInterface;
use Cake\Http\ServerRequest;

class RequestPolicy implements RequestPolicyInterface
{
    public function canAccess($identity, ServerRequest $request)
    {
        if ($request->getParam('controller') === 'Articles'
            && $request->getParam('action') === 'index'
        ) {
            return true;
        }

        return false;
    }
}
```

`Application::getAuthorizationService()` で request を policy にマップします。

```php
use App\Policy\RequestPolicy;
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Middleware\RequestAuthorizationMiddleware;
use Authorization\Policy\MapResolver;
use Cake\Http\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
{
    $mapResolver = new MapResolver();
    $mapResolver->map(ServerRequest::class, RequestPolicy::class);

    return new AuthorizationService($mapResolver);
}
```

```php
public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
{
    $middlewareQueue->add(new AuthorizationMiddleware($this));
    $middlewareQueue->add(new RequestAuthorizationMiddleware());

    return $middlewareQueue;
}
```
