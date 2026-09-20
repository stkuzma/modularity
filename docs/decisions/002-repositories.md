# 002. Repositories at module boundaries

## Context

Every processor needs persistence. Calling Eloquent directly is shorter, and
for most Laravel applications it is also correct.

## Decision

Each module states the persistence it needs as an interface and ships one
Eloquent implementation. The interface exists at the module boundary, not on
every model.

## Why

Not to hide Eloquent, and not to make the database swappable, which I have
never once needed. It buys one thing: a processor can be unit tested with an in-memory
fake and no application booted. `UserRepositoryContract` then runs the same
suite against both implementations, so the fake is provably equivalent rather
than hopefully equivalent.

## Why not Eloquent directly

It would be less code. The tests would need a database, they would be slower,
and the module would have no stated surface for another module to depend on.

## Internal, not exported

A repository is an implementation detail that happens to be an interface. It
grows whenever the owning module needs a new query, so exporting it would let
a module's public surface widen by accident. Other modules consume a capability
contract instead; see [ADR 009](009-module-public-api.md).

## Cost

Five extra artefacts per aggregate: interface, implementation, binding, fake,
contract test. That is a real tax, and it is only worth paying where a module
boundary or a test seam actually exists. It is not applied to everything.
