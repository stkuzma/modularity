# Decisions

Short records of the choices this repository makes, each with what it costs and
what was rejected. They exist because "why not the simpler thing" is the useful
half of an architecture, and it is the half that usually goes unwritten.

| | Decision |
|---|---|
| [001](001-modular-monolith.md) | A modular monolith, not services and not a plain Laravel app |
| [002](002-repositories.md) | Repositories at module boundaries only |
| [003](003-processors-and-presenters.md) | Processor and Presenter are conventions, not behaviour |
| [004](004-how-modules-talk.md) | Contracts for what you need, events for what happened |
| [005](005-migrations.md) | Module-owned, expand and contract |
| [006](006-failure-directions.md) | Authorization fails closed, sign-in fails open |
| [007](007-local-totp.md) | TOTP written out, and why not in production |
| [008](008-audit-outside-the-transaction.md) | The audit record is not part of the transaction |
| [009](009-module-public-api.md) | A module exports capabilities, not its persistence shape |
