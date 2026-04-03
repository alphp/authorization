# AuthorizationComponent

`AuthorizationComponent` fournit des méthodes de convenance pour vérifier les
permissions depuis les contrôleurs. Il s'appuie sur le middleware déjà chargé.

```php
public function initialize(): void
{
    parent::initialize();
    $this->loadComponent('Authorization.Authorization');
}
```

## Vérifications Automatiques d'Autorisation

```php
$this->Authorization->authorizeModel('index', 'add');
```

Pour rendre certaines actions publiques :

```php
$this->loadComponent('Authorization.Authorization', [
    'skipAuthorization' => [
        'login',
    ]
]);
```

## Vérifier l'Autorisation

```php
public function edit($id)
{
    $article = $this->Articles->get($id);
    $this->Authorization->authorize($article);
}
```

Vous pouvez préciser une action de policy :

```php
$this->Authorization->authorize($article, 'update');
```

Ou récupérer un booléen :

```php
if ($this->Authorization->can($article, 'update')) {
    // Faire quelque chose
}
```

## Utilisateurs Anonymes

`can()` et `authorize()` supportent les utilisateurs anonymes. Les policies
reçoivent `null` comme utilisateur si personne n'est connecté.

## Appliquer les Scopes des Policies

```php
$query = $this->Authorization->applyScope($this->Articles->find());
```

Mappez des actions vers d'autres noms de méthodes :

```php
$this->Authorization->mapActions([
    'index' => 'list',
    'delete' => 'remove',
    'add' => 'insert',
]);
```

## Sauter l'Autorisation

```php
public function view($id)
{
    $this->Authorization->skipAuthorization();
}
```
