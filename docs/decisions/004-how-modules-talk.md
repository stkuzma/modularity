# 004. How modules talk

## Context

The hardest question about a modular monolith is not how to draw boundaries,
it is what to do when one module needs something from another.

## Decision

Two default mechanisms:

- **A contract**, for something you need now. `Invitations` creates an account
  through `Users\Contracts\UserRepository` and never sees that module's model.
  It returns its own `Invitation`; the new user's id travels on an event.
- **An event**, for something that happened. `Users` announces `UserSuspended`
  and knows nothing about what follows. `Auth` listens and revokes that
  account's tokens. Delete `Auth` and suspension still works, it just stops
  revoking tokens.

A read-only contract is a third shape rather than an exception to the first
two: `AccessChecker::permissionsFor()` is a query port, owned by the core,
answered by whichever module can. Calling it a contract is accurate but flat;
it is worth naming separately when a module needs a fact rather than an action.

## Why not a command or query bus

It would add indirection and a registry, and it would make every call site
harder to follow, in exchange for decoupling this application does not need at
four modules. A bus earns its place when dispatch has to be asynchronous,
retried or routed. None of that is true here yet.

## Known gap

Every event here is dispatched synchronously and handled in-process. Nothing in
this repository demonstrates a queued listener, a retry or a failed job. For
token revocation on suspension, synchronous is the correct choice, because the
revocation is the security boundary. For anything slower it would not be, and
that case is not shown.
