# 009. A module's public API is capabilities, not storage

## Context

`Invitations` needs to create an account. The shortest path was to depend on
`Users\Contracts\UserRepository`, which is an interface the Users module
already had.

It worked, and it was wrong. That contract says "I am a repository". It does
not say "I can register an account". Depending on it coupled one module to the
*shape of another module's persistence*, and it meant every method added to
that repository for internal reasons silently widened the public surface.

## Decision

A module exports capability contracts. `Users\Contracts\Accounts`:

```php
interface Accounts
{
    public function isRegistered(string $email): bool;

    /** @return int the new account's id */
    public function register(CreateUserData $data): int;
}
```

`UserRepository` stays internal to `Users`. The rule is enforced:
`ArchitectureTest::a_module_does_not_export_its_persistence_shape` fails the
build when one module imports another's `*Repository` contract.

## Why the distinction matters

The two interfaces differ in what a change to them means. A repository grows
whenever the owning module needs a new query; a capability contract grows only
when the module decides to offer something new. One is an implementation
detail that happens to be an interface, the other is a promise.

## Cost

An extra interface and a small service that mostly delegates. For a module
nobody else consumes, this would be pure overhead, and `Access` and `Auth` do
not have one: they are consumed through events, or not at all.
