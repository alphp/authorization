# Policies

Policies は与えられたオブジェクトの権限を解決するクラスです。

## Policies を作成する

`src/Policy` ディレクトリに policy を作成します。解決方法については
[ポリシーリゾルバー](./policy-resolvers) を参照してください。

```php
<?php
namespace App\Policy;

use App\Model\Entity\Article;
use Authorization\IdentityInterface;

class ArticlePolicy
{
}
```

クエリやテーブルにも policy を定義できます。

```bash
bin/cake bake policy --type entity Article
bin/cake bake policy --type table Articles
```

## ポリシーメソッドの書き方

```php
public function canUpdate(IdentityInterface $user, Article $article)
{
    return $user->id == $article->user_id;
}
```

## ポリシーの Result オブジェクト

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

## ポリシーのスコープ

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

## ポリシーの前提条件

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
