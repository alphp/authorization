# 3.0 Migration Guide

Authorization 3.0 includes new features and several breaking changes.

## Breaking Changes

The following interfaces now have explicit parameter and return types:

- `AuthorizationServiceInterface`
- `IdentityInterface`
- `BeforePolicyInterface`
- `RequestPolicyInterface`
- `ResolverInterface`

## Multiple Optional Arguments

The following methods were updated to accept and pass through multiple optional
arguments:

- `IdentityInterface::applyScope`
- `AuthorizationServiceInterface::applyScope`
- `AuthorizationServiceInterface::can`
- `AuthorizationServiceInterface::canResult`

## Removed Methods

- `AuthorizationService::resultTypeCheck`, replaced by an `assert()` call.
