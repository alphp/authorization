# Middleware d'Autorisation de Requête

Ce middleware permet d'autoriser la requête elle-même, par exemple selon le
contrôleur, l'action, ou tout autre système ACL ou RBAC.

Ajoutez-le après `AuthorizationMiddleware`, `AuthenticationMiddleware` et
`RoutingMiddleware`.

## Comment l'utiliser

Créez `src/Policy/RequestPolicy.php` :

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

Mappez ensuite la classe de requête à cette policy :

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

Puis chargez `RequestAuthorizationMiddleware` après `AuthorizationMiddleware` :

```php
public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
{
    $middlewareQueue->add(new AuthorizationMiddleware($this));
    $middlewareQueue->add(new RequestAuthorizationMiddleware());

    return $middlewareQueue;
}
```
