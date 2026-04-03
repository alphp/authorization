# Middleware Authorization

Le plugin Authorization s'intègre à votre application sous la forme d'un
middleware. `AuthorizationMiddleware` :

- décore l'`identity` de la requête avec `can`, `canResult` et `applyScope` si
  nécessaire ;
- vérifie qu'une autorisation a bien été effectuée ou contournée.

Exemple de base :

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

## Décorateur d'Identity

Par défaut, l'`identity` de la requête est enveloppée par
`Authorization\IdentityDecorator`.

```php
$originalUser = $user->getOriginalData();
```

Si vous utilisez le plugin Authentication, `Authorization\Identity` sera utilisé
et implémentera également `Authentication\IdentityInterface`.

### Utiliser votre classe User comme Identity

Si votre classe utilisateur implémente déjà `Authorization\IdentityInterface`,
vous pouvez éviter le décorateur :

```php
$middlewareQueue->add(new AuthorizationMiddleware($this, [
    'identityDecorator' => function ($auth, $user) {
        return $user->setAuthorization($auth);
    }
]));
```

## S'assurer que l'Autorisation est Appliquée

Par défaut, une `AuthorizationRequiredException` est levée si une requête avec
`identity` n'a pas été autorisée ni contournée.

```php
$middlewareQueue->add(new AuthorizationMiddleware($this, [
    'requireAuthorizationCheck' => false
]));
```

## Gérer les Requêtes Non Autorisées

Les gestionnaires intégrés sont :

- `Exception`
- `Redirect`
- `CakeRedirect`

Exemple :

```php
use Authorization\Exception\MissingIdentityException;

$middlewareQueue->add(new AuthorizationMiddleware($this, [
    'unauthorizedHandler' => [
        'className' => 'Authorization.Redirect',
        'url' => '/pages/accesinterdit',
        'queryParam' => 'redirectUrl',
        'exceptions' => [
            MissingIdentityException::class,
            OtherException::class,
        ],
    ],
]));
```

Vous pouvez aussi créer votre propre gestionnaire en étendant
`RedirectHandler` si vous devez ajouter un message flash ou une logique
supplémentaire.
