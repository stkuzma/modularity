# 005. Module-owned, expand and contract

## Context

A central `database/migrations` directory makes a module impossible to remove:
its schema history stays behind and nothing says which file belonged to what.

## Decision

Migrations live in the module. `database/migrations` holds only framework
tables. Schema changes to anything already deployed are expand-only: add the
nullable column, backfill, start writing, make it required, drop the old one,
across separate releases.

Backfills are idempotent console commands, never migrations. A migration that
loops over rows holds a lock and cannot be resumed.

## Why

The expand and contract rule is not academic here: it is what makes the
blue-green rollout possible. Both colours run against the same database during
a switch, so the schema has to satisfy the old code and the new code at once.
A destructive migration breaks the colour that is still serving.

## Cost

A column rename takes four releases instead of one. On a system with no
downtime budget that is the price; on a system that can take a maintenance
window it is overhead.
