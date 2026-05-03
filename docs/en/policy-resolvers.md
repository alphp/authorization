# Policy Resolvers

Policy resolvers map resource objects to their corresponding policy classes.
The plugin includes a few resolvers out of the box, and you can implement your
own by implementing `Authorization\Policy\ResolverInterface`.

Built-in resolvers:

- `MapResolver` maps resource class names to policy class names, policy
  objects, or callables.
- `OrmResolver` applies convention-based policy resolution for common ORM
  objects.
- `ResolverCollection` combines multiple resolvers and checks them in order.

## Using MapResolver

`MapResolver` lets you map resource classes to policy classes, policy
instances, or factory callables:

```php
use Authorization\Policy\MapResolver;

$mapResolver = new MapResolver();

// Map a resource class to a policy classname
$mapResolver->map(Article::class, ArticlePolicy::class);

// Map a resource class to a policy instance
$mapResolver->map(Article::class, new ArticlePolicy());

// Map a resource class to a factory callable
$mapResolver->map(Article::class, function ($resource, $mapResolver) {
    // Return a policy object.
});
```

## Using OrmResolver

`OrmResolver` is the convention-based resolver for CakePHP ORM objects. It
assumes:

1. Policies live in `App\Policy`.
2. Policy classes use the `Policy` suffix.

`OrmResolver` can resolve policies for:

- Entities, using the entity class name.
- Tables, using the table class name.
- Queries, using the class returned by `repository()`.

These rules are applied:

1. The resource class name is converted into a policy class name. For example,
   `App\Model\Entity\Bookmark` maps to `App\Policy\BookmarkPolicy`.
2. Plugin resources first check for an application override such as
   `App\Policy\Bookmarks\BookmarkPolicy` for
   `Bookmarks\Model\Entity\Bookmark`.
3. If no application override exists, the plugin policy is checked, such as
   `Bookmarks\Policy\BookmarkPolicy`.

For tables, `App\Model\Table\ArticlesTable` maps to
`App\Policy\ArticlesTablePolicy`. Queries use their repository table to derive
the policy class.

Customize `OrmResolver` through its constructor:

```php
use Authorization\Policy\OrmResolver;

$appNamespace = 'App';
$overrides = [
    'Blog' => 'Cms',
];

$resolver = new OrmResolver($appNamespace, $overrides);
```

## Using Multiple Resolvers

`ResolverCollection` combines resolvers and checks them sequentially:

```php
use Authorization\Policy\ResolverCollection;
use Authorization\Policy\MapResolver;
use Authorization\Policy\OrmResolver;

$ormResolver = new OrmResolver();
$mapResolver = new MapResolver();

$resolver = new ResolverCollection([$mapResolver, $ormResolver]);
```

This lets you check the explicit map first and fall back to ORM conventions.

## Creating a Resolver

Implement `Authorization\Policy\ResolverInterface` and define
`getPolicy($resource)` when you need custom resolution logic.

One example is bridging the Authorization plugin with legacy controller-based
authorization during migration from `AuthComponent`. First create a catch-all
policy:

```php
// in src/Policy/ControllerHookPolicy.php
namespace App\Policy;

class ControllerHookPolicy
{
    public function __call(string $name, array $arguments)
    {
        /** @var ?\Authorization\Identity $user */
        [$user, $controller] = $arguments;

        return $controller->isAuthorized($user?->getOriginalData());
    }
}
```

Then create a resolver for controllers:

```php
// in src/Policy/ControllerResolver.php
namespace App\Policy;

use Authorization\Policy\Exception\MissingPolicyException;
use Authorization\Policy\ResolverInterface;
use Cake\Controller\Controller;

class ControllerResolver implements ResolverInterface
{
    public function getPolicy($resource)
    {
        if ($resource instanceof Controller) {
            return new ControllerHookPolicy();
        }

        throw new MissingPolicyException([get_class($resource)]);
    }
}
```

You can register this resolver directly or combine it with others through
`ResolverCollection`.
