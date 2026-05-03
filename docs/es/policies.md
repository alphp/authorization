# Policy

Policy son clases que resuelven los permisos para un objeto determinado.

## Creando una Policy

Puede crear una policy en `src/Policy`.

```php
<?php
namespace App\Policy;

use App\Model\Entity\Article;
use Authorization\IdentityInterface;

class ArticlePolicy
{
}
```

También puede generar classes vacías con Bake:

```bash
bin/cake bake policy --type entity Article
bin/cake bake policy --type table Articles
```

## Escribiendo Métodos Policy

```php
public function canUpdate(IdentityInterface $user, Article $article)
{
    return $user->id == $article->user_id;
}
```

## Objetos Policy Result

```php
use Authorization\Policy\Result;

public function canUpdate(IdentityInterface $user, Article $article)
{
    if ($user->id == $article->user_id) {
        return new Result(true);
    }

    return new Result(false, 'not-owner');
}
```

## Alcances Policy

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
