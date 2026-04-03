# AuthorizationComponent

`AuthorizationComponent` は controller で権限確認を行うための
ヘルパーメソッドを提供します。

```php
public function initialize(): void
{
    parent::initialize();
    $this->loadComponent('Authorization.Authorization');
}
```

## 自動認可の確認

```php
$this->Authorization->authorizeModel('index', 'add');
```

公開アクションを設定する場合:

```php
$this->loadComponent('Authorization.Authorization', [
    'skipAuthorization' => [
        'login',
    ]
]);
```

## Authorization を確認する

```php
public function edit($id)
{
    $article = $this->Articles->get($id);
    $this->Authorization->authorize($article);
}
```

別の policy アクション名を使う場合:

```php
$this->Authorization->authorize($article, 'update');
```

bool が欲しい場合:

```php
if ($this->Authorization->can($article, 'update')) {
    // 何らかの処理
}
```

## 匿名ユーザー

`can()` と `authorize()` は匿名ユーザーも扱えます。未ログインの場合、
policy には `null` が渡されます。

## ポリシースコープを適用する

```php
$query = $this->Authorization->applyScope($this->Articles->find());
```

```php
$this->Authorization->mapActions([
    'index' => 'list',
    'delete' => 'remove',
    'add' => 'insert',
]);
```

## 認可をスキップする

```php
public function view($id)
{
    $this->Authorization->skipAuthorization();
}
```
