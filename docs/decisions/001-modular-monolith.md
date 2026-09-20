# 001. A modular monolith

## Context

One team, one deployable, a domain that is expected to grow. The failure mode
to avoid is the one every Laravel application reaches eventually: everything
can reach everything, and the boundaries exist only in the README.

## Decision

One application, one database, modules with enforced boundaries. Cross-module
access is limited to `Contracts`, `Data`, `Enums` and `Events`, and a test in
CI fails the build when that is violated.

## Why not microservices

There is no scaling problem here and no team boundary to mirror. Splitting
would buy network calls, distributed transactions and deployment coordination
in exchange for boundaries this repository gets from a test. If a module ever
does need to become a service, the interface it already sits behind is the
seam.

## Why not a plain Laravel application

Because I wanted the boundary enforced rather than agreed. Without a check,
"modules" are folders, and folders stop meaning anything under deadline.

## Cost

More files than the conventional layout: a provider, a contract and a DTO per
module that a small application would not need. For a small CRUD application
this architecture is the wrong choice and the conventional layout is better.
