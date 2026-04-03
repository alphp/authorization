# Résolveurs de Policy

Les résolveurs de policy font correspondre les ressources à leurs classes de
policy. Le plugin fournit plusieurs résolveurs prêts à l'emploi et vous pouvez
créer le vôtre en implémentant `Authorization\Policy\ResolverInterface`.

Résolveurs intégrés :

- `MapResolver`
- `OrmResolver`
- `ResolverCollection`

## Utiliser MapResolver

```php
use Authorization\Policy\MapResolver;

$mapResolver = new MapResolver();
$mapResolver->map(Article::class, ArticlePolicy::class);
$mapResolver->map(Article::class, new ArticlePolicy());
$mapResolver->map(Article::class, function ($resource, $mapResolver) {
    // Renvoyer un objet policy.
});
```

## Utiliser OrmResolver

`OrmResolver` repose sur les conventions suivantes :

1. Les policies se trouvent dans `App\Policy`.
2. Les classes de policy se terminent par `Policy`.

Il peut résoudre des policies pour :

- les entités ;
- les tables ;
- les queries.

Exemples de correspondance :

- `App\Model\Entity\Bookmark` devient `App\Policy\BookmarkPolicy`
- `App\Model\Table\ArticlesTable` devient `App\Policy\ArticlesTablePolicy`

Les ressources de plugin vérifient d'abord une éventuelle policy de
l'application, puis une policy du plugin lui-même.

Configuration personnalisée :

```php
use Authorization\Policy\OrmResolver;

$appNamespace = 'App';
$overrides = [
    'Blog' => 'Cms',
];

$resolver = new OrmResolver($appNamespace, $overrides);
```

## Utiliser ResolverCollection

```php
use Authorization\Policy\MapResolver;
use Authorization\Policy\OrmResolver;
use Authorization\Policy\ResolverCollection;

$resolver = new ResolverCollection([
    new MapResolver(),
    new OrmResolver(),
]);
```

## Créer un Resolver

Implémentez `Authorization\Policy\ResolverInterface` et définissez
`getPolicy($resource)` lorsque vous avez besoin d'une logique de résolution
personnalisée.
