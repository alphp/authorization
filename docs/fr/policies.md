# Policies

Les policies sont des classes qui résolvent les permissions pour une ressource
donnée. Vous pouvez créer des policies pour toute classe de votre application
qui doit être soumise à des vérifications d'autorisation.

## Créer des Policies

Placez vos policies dans `src/Policy`. Les classes de policy n'ont pas de
classe de base obligatoire. Les classes de l'application sont résolues vers une
policy correspondante par un résolveur. Consultez
[Résolveurs de Policy](./policy-resolvers).

Exemple dans `src/Policy/ArticlePolicy.php` :

```php
<?php
namespace App\Policy;

use App\Model\Entity\Article;
use Authorization\IdentityInterface;

class ArticlePolicy
{
}
```

Les tables et les queries peuvent aussi avoir des policies. Une classe
`App\Model\Table\ArticlesTable` correspondra à
`App\Policy\ArticlesTablePolicy`.

Vous pouvez générer des classes vides avec Bake :

```bash
bin/cake bake policy --type entity Article
bin/cake bake policy --type table Articles
```

## Écrire des Méthodes de Policy

```php
public function canUpdate(IdentityInterface $user, Article $article)
{
    return $user->id == $article->user_id;
}
```

Une méthode de policy doit renvoyer `true` ou un objet `Result`. Toute autre
valeur est interprétée comme un échec.

Le paramètre `$user` peut être `null` pour les utilisateurs non authentifiés.

## Objets Result d'une Policy

```php
use Authorization\Policy\Result;

public function canUpdate(IdentityInterface $user, Article $article)
{
    if ($user->id == $article->user_id) {
        return new Result(true);
    }

    return new Result(false, 'non-proprietaire');
}
```

Toute valeur autre que `true` ou un `ResultInterface` est considérée comme un
échec.

## Portées de Policy

Les scopes permettent de modifier une query ou une autre ressource pour n'y
laisser que ce que l'utilisateur courant peut voir :

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

## Pré-conditions de Policy

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

`before()` peut renvoyer :

- `true` pour autoriser immédiatement.
- `false` pour refuser immédiatement.
- `null` pour laisser la méthode normale de policy décider.
