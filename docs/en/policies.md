# Policies

Policies are classes that resolve permissions for a given object. You can
create policies for any class in your application that needs authorization
checks.

## Creating Policies

Create policies in `src/Policy`. They do not need to extend a base class or
implement a shared interface. Application classes are resolved to matching
policy classes by a policy resolver. See [Policy Resolvers](/policy-resolvers)
for the available approaches.

Most applications use the `Policy` suffix in `src/Policy`. For example, an
article entity policy in `src/Policy/ArticlePolicy.php`:

```php
<?php
namespace App\Policy;

use App\Model\Entity\Article;
use Authorization\IdentityInterface;

class ArticlePolicy
{
}
```

Policies can also be resolved for tables and queries. Query objects use their
`repository()` method to derive the table class. For example,
`App\Model\Table\ArticlesTable` maps to
`App\Policy\ArticlesTablePolicy`.

You can generate empty ORM policy classes with Bake:

```bash
# Create an entity policy
bin/cake bake policy --type entity Article

# Create a table policy
bin/cake bake policy --type table Articles
```

## Writing Policy Methods

Define methods that answer whether a user may perform an action:

```php
public function canEdit(IdentityInterface $user, Article $article): bool
{
    return $user->id === $article->user_id;
}
```

Policy methods must return `true` or a `Result` object to indicate success. Any
other return value is treated as failure.

Policy methods receive `null` for `$user` when evaluating anonymous users. If
you want anonymous users to fail automatically, typehint
`IdentityInterface`.

## Policy Result Objects

Besides booleans, policy methods may return `Authorization\Policy\Result` for
additional context:

```php
use Authorization\Policy\Result;

public function canUpdate(IdentityInterface $user, Article $article): Result
{
    if ($user->id === $article->user_id) {
        return new Result(true);
    }

    return new Result(false, 'not-owner');
}
```

Any return value other than `true` or a `ResultInterface` instance is
considered a failure.

## Policy Scopes

Policies can also define scopes that modify another object with authorization
conditions. A common use case is restricting list views to the current user:

```php
namespace App\Policy;

class ArticlesTablePolicy
{
    public function scopeIndex($user, $query)
    {
        return $query->where(['Articles.user_id' => $user->getIdentifier()]);
    }
}
```

## Policy Pre-conditions

Use `BeforePolicyInterface` when you want common checks across every operation
in a policy:

```php
namespace App\Policy;

use Authorization\IdentityInterface;
use Authorization\Policy\BeforePolicyInterface;
use Authorization\Policy\ResultInterface;

class ArticlesPolicy implements BeforePolicyInterface
{
    public function before(?IdentityInterface $identity, mixed $resource, string $action): ResultInterface|bool|null
    {
        if ($identity->getOriginalData()->is_admin) {
            return true;
        }

        return null;
    }
}
```

The `before()` hook can return:

- `true` to allow the action immediately.
- `false` to deny the action immediately.
- `null` to let the normal authorization method run.

## Scope Pre-conditions

Scopes support similar pre-conditions through `BeforeScopeInterface`:

```php
namespace App\Policy;

use Authorization\Policy\BeforeScopeInterface;

class ArticlesTablePolicy implements BeforeScopeInterface
{
    public function beforeScope($user, $query, $action)
    {
        if ($user->getOriginalData()->is_trial_user) {
            return $query->where(['Articles.is_paid_only' => false]);
        }

        return null;
    }
}
```

Returning a modified resource short-circuits the normal scope method. Returning
`null` allows the scope method to run normally.

## Applying Policies

See [Applying Policy Scopes](/component#applying-policy-scopes) for controller
examples.
