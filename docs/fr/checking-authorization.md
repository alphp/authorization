# Vérifier une Autorisation

Une fois le [middleware](./middleware) installé et une `identity` ajoutée à la
requête, vous pouvez commencer à vérifier les autorisations.

L'`identity` peut être transmise à vos modèles, services ou templates afin de
faire des vérifications partout dans l'application.

## Vérifier l'Autorisation pour une Seule Ressource

```php
$user = $this->request->getAttribute('identity');

if ($user->can('delete', $article)) {
    // Effectuer la suppression
}
```

Si vos policies renvoient un objet résultat :

```php
$result = $user->canResult('delete', $article);
if ($result->getStatus()) {
    // Procéder à l'effacement
}
```

## Appliquer des Conditions de Portée

```php
$user = $this->request->getAttribute('identity');
$query = $user->applyScope('index', $query);
```

Dans les contrôleurs, le [component](./component) permet d'automatiser les
vérifications qui doivent lever une exception en cas d'échec.
