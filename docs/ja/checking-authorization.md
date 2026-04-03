# 認可の確認

[認可ミドルウェア](./middleware) を適用して request に `identity` を
追加したら、認可チェックを開始できます。

## 単一リソースの認可チェック

```php
$user = $this->request->getAttribute('identity');

if ($user->can('delete', $article)) {
    // 削除処理
}
```

結果オブジェクトを返す policy の場合:

```php
$result = $user->canResult('delete', $article);
if ($result->getStatus()) {
    // 削除する
}
```

## スコープ条件を適用する

```php
$user = $this->request->getAttribute('identity');
$query = $user->applyScope('index', $query);
```

controller では [AuthorizationComponent](./component) を使って失敗時に
例外を投げるチェックを簡単に行えます。
