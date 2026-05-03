# Authorization Middleware

Authorization is applied as middleware. `AuthorizationMiddleware` handles two
main responsibilities:

- Decorating the request `identity` with `can`, `canResult`, and `applyScope`
  methods when needed.
- Ensuring each request has authorization checked or explicitly bypassed.

To use the middleware, implement
`AuthorizationServiceProviderInterface` in your application class, then add the
middleware to the queue.

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

The authorization service requires a policy resolver. See [Policies](/policies)
and [Policy Resolvers](/policy-resolvers) for the available options.

## Identity Decorator

By default, the request `identity` is wrapped in
`Authorization\IdentityDecorator`. The decorator proxies method calls, array
access, and property access to the underlying identity. Use
`getOriginalData()` to retrieve the original value:

```php
$originalUser = $user->getOriginalData();
```

If you use the
[cakephp/authentication](https://github.com/cakephp/authentication) plugin,
`Authorization\Identity` is used instead. It implements both
`Authentication\IdentityInterface` and `Authorization\IdentityInterface`, so
the decorated identity works with Authentication helpers and components.

### Using Your User Class as the Identity

If your existing user class already represents identity data, implement
`Authorization\IdentityInterface` and use the `identityDecorator` option to
skip the wrapper:

```php
namespace App\Model\Entity;

use Authorization\AuthorizationServiceInterface;
use Authorization\IdentityInterface;
use Authorization\Policy\ResultInterface;
use Cake\ORM\Entity;

class User extends Entity implements IdentityInterface
{
    public function can(string $action, mixed $resource): bool
    {
        return $this->authorization->can($this, $action, $resource);
    }

    public function canResult(string $action, mixed $resource): ResultInterface
    {
        return $this->authorization->canResult($this, $action, $resource);
    }

    public function applyScope(string $action, mixed $resource, mixed ...$optionalArgs): mixed
    {
        return $this->authorization->applyScope($this, $action, $resource, ...$optionalArgs);
    }

    public function getOriginalData(): \ArrayAccess|array
    {
        return $this;
    }

    public function setAuthorization(AuthorizationServiceInterface $service): static
    {
        $this->authorization = $service;

        return $this;
    }
}
```

Then configure the middleware:

```php
$middlewareQueue->add(new AuthorizationMiddleware($this, [
    'identityDecorator' => function ($auth, $user) {
        return $user->setAuthorization($auth);
    },
]));
```

If you also use Authentication, implement both interfaces:

```php
use Authentication\IdentityInterface as AuthenticationIdentity;
use Authorization\IdentityInterface as AuthorizationIdentity;

class User extends Entity implements AuthorizationIdentity, AuthenticationIdentity
{
    public function getIdentifier(): int|string|null
    {
        return $this->id;
    }
}
```

## Ensuring Authorization Is Applied

By default, `AuthorizationMiddleware` verifies that each request with an
`identity` also performed or bypassed authorization checks. If not,
`AuthorizationRequiredException` is raised after controller and middleware
execution completes.

This does not prevent unauthorized access by itself, but it is valuable during
development and testing. You can disable the requirement:

```php
$middlewareQueue->add(new AuthorizationMiddleware($this, [
    'requireAuthorizationCheck' => false,
]));
```

You can also use a Closure to conditionally skip the authorization check based
on the request. This is useful when you need to bypass authorization for specific
routes (e.g., plugin admin panels that manage their own authorization):

```php
$middlewareQueue->add(new AuthorizationMiddleware($this, [
    'requireAuthorizationCheck' => function ($request) {
        // Skip authorization check for specific routes
        $path = $request->getUri()->getPath();
        if (str_contains($path, '/admin/queue')) {
            return false;
        }

        return true;
    }
]));
```

The Closure receives the `ServerRequestInterface` and should return a boolean.
Return `true` to require authorization check (default behavior), or `false`
to skip the check for that request.

## Handling Unauthorized Requests

By default, authorization exceptions are rethrown. You can configure handlers
for unauthorized requests, such as redirecting a user to a login page.

Built-in handlers:

- `Exception`, which rethrows the exception.
- `Redirect`, which redirects to a plain URL.
- `CakeRedirect`, which redirects using CakePHP router syntax.

Shared redirect options:

- `url`, the redirect target.
- `exceptions`, the exception classes that trigger redirect handling.
- `queryParam`, the query string key used for the original URL. Defaults to
  `redirect`.
- `statusCode`, the redirect status code. Defaults to `302`.
- `allowedRedirectExtensions`, a whitelist of file extensions that may still
  redirect. Set this carefully to avoid redirecting API-style requests.

Example:

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
        'allowedRedirectExtensions' => ['csv', 'pdf'],
    ],
]));
```

All handlers receive the thrown exception, which is always an instance of
`Authorization\Exception\Exception`.

To handle more exception types gracefully, add them to `exceptions`:

```php
'exceptions' => [
    MissingIdentityException::class,
    ForbiddenException::class,
],
```

## Adding a Flash Message After Redirect

If you need to add a flash message or custom side effects on redirect, create
your own unauthorized handler based on the built-in redirect handler.

Create `src/Middleware/UnauthorizedHandler/CustomRedirectHandler.php`:

```php
<?php
declare(strict_types=1);

namespace App\Middleware\UnauthorizedHandler;

use Authorization\Exception\Exception;
use Authorization\Middleware\UnauthorizedHandler\RedirectHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CustomRedirectHandler extends RedirectHandler
{
    public function handle(Exception $exception, ServerRequestInterface $request, array $options = []): ResponseInterface
    {
        $response = parent::handle($exception, $request, $options);
        $request->getFlash()->error('You are not authorized to access that location');

        return $response;
    }
}
```

Then reference it from your middleware configuration:

```php
use Authorization\Exception\ForbiddenException;
use Authorization\Exception\MissingIdentityException;

$middlewareQueue->add(new AuthorizationMiddleware($this, [
    'unauthorizedHandler' => [
        'className' => 'CustomRedirect',
        'url' => '/users/login',
        'queryParam' => 'redirectUrl',
        'exceptions' => [
            MissingIdentityException::class,
            ForbiddenException::class,
        ],
        'custom_param' => true,
    ],
]));
```

Any additional handler configuration is available in the `$options` array
received by `handle()`.
