# 2.0 Migration Guide

Authorization 2.0 introduced new features and a few breaking changes.

## Breaking Changes

`IdentityInterface` gained type declarations. If you implement that interface
in your application, update your implementation to match the new signatures.

`IdentityInterface` also gained `canResult()`. It always returns a
`ResultInterface`, while `can()` now always returns a boolean. In 1.x, `can()`
could return either a boolean or a `ResultInterface`, which made it difficult
to rely on consistently.
