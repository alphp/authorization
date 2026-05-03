# Checking Authorization

Once you have applied the [middleware](/middleware) and attached an `identity`
to the request, you can start checking authorization. The middleware decorates
the request identity with authorization-related helper methods.

You can pass the `identity` into models, services, or templates and check
permissions anywhere in your application. See the
[identity decorator section](/middleware#identity-decorator) for customization
options.

## Checking Authorization for a Single Resource

Use `can()` to check authorization for a single resource, typically an ORM
entity or domain object:

```php
$user = $this->request->getAttribute('identity');

if ($user->can('delete', $article)) {
    // Do delete operation
}
```

If your policies return result objects, use `canResult()` and inspect the
status:

```php
$result = $user->canResult('delete', $article);
if ($result->getStatus()) {
    // Do deletion
}
```

## Applying Scope Conditions

When working with collections such as paginated queries, apply authorization
conditions through scopes so only accessible records are returned:

```php
$user = $this->request->getAttribute('identity');
$query = $user->applyScope('index', $query);
```

In controller actions, [AuthorizationComponent](/component) can streamline
checks that should raise exceptions on failure.
