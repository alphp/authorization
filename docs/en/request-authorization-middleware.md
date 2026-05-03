# Request Authorization Middleware

This middleware is useful when you want to authorize requests themselves, for
example each controller and action, against a role-based access system or some
other process that controls access to routes.

Add it after `AuthorizationMiddleware`, `AuthenticationMiddleware`, and
`RoutingMiddleware`.

The authorization logic for the request is implemented in a request policy. You
can keep all request-level logic there or delegate to an ACL or RBAC layer.

## Using It

Create a policy for request objects in `src/Policy/RequestPolicy.php`:

```php
namespace App\Policy;

use Authorization\Policy\RequestPolicyInterface;
use Authorization\Policy\ResultInterface;
use Cake\Http\ServerRequest;

class RequestPolicy implements RequestPolicyInterface
{
    public function canAccess($identity, ServerRequest $request): bool|ResultInterface
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

Map the request class to the policy inside
`Application::getAuthorizationService()`:

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

Then load `RequestAuthorizationMiddleware` after `AuthorizationMiddleware`:

```php
public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
{
    $middlewareQueue->add(new AuthorizationMiddleware($this));
    $middlewareQueue->add(new RequestAuthorizationMiddleware());

    return $middlewareQueue;
}
```

## Controller Usage

If fallback routing is enabled, asset 404s and missing controllers can also hit
`RequestAuthorizationMiddleware`. In that case you can attach it only to your
application controller:

```php
public function initialize(): void
{
    parent::initialize();

    $this->middleware(function ($request, $handler): ResponseInterface {
        $config = [
            'unauthorizedHandler' => [
                // ...
            ],
        ];
        $middleware = new RequestAuthorizationMiddleware($config);

        return $middleware->process($request, $handler);
    });
}
```

If you do this, settings such as `DebugKit.ignoreAuthorization` may also need
to be enabled.
