# ポリシーリゾルバー

ポリシーリゾルバーは、リソースオブジェクトと対応する policy クラスを
結びつけます。必要に応じて独自の resolver を実装できます。

組み込みの resolver:

- `MapResolver`
- `OrmResolver`
- `ResolverCollection`

## MapResolver を使う

```php
use Authorization\Policy\MapResolver;

$mapResolver = new MapResolver();
$mapResolver->map(Article::class, ArticlePolicy::class);
$mapResolver->map(Article::class, new ArticlePolicy());
$mapResolver->map(Article::class, function ($resource, $mapResolver) {
    // Return a policy object.
});
```

## OrmResolver を使う

`OrmResolver` は CakePHP ORM 用の規約ベース resolver です。

1. policy は `App\Policy` に置きます。
2. policy クラス名は `Policy` で終わります。

例:

- `App\Model\Entity\Bookmark` -> `App\Policy\BookmarkPolicy`
- `App\Model\Table\ArticlesTable` -> `App\Policy\ArticlesTablePolicy`

```php
use Authorization\Policy\OrmResolver;

$appNamespace = 'App';
$overrides = [
    'Blog' => 'Cms',
];

$resolver = new OrmResolver($appNamespace, $overrides);
```

## ResolverCollection を使う

```php
use Authorization\Policy\MapResolver;
use Authorization\Policy\OrmResolver;
use Authorization\Policy\ResolverCollection;

$resolver = new ResolverCollection([
    new MapResolver(),
    new OrmResolver(),
]);
```

## Resolver を作成する

独自の実装が必要な場合は `Authorization\Policy\ResolverInterface` を
実装して `getPolicy($resource)` を定義します。
