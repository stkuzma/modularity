# 008. The audit record is not part of the transaction

## Context

`AcceptInvitation` writes a user and marks an invitation inside one
transaction, then records an audit entry. The entry could equally sit inside
the transaction.

## Decision

The audit write happens after the commit, everywhere.

## Why

Coupling them means a failed audit write rolls back the business operation. An
invitation would be rejected because a log row could not be written, which is
the wrong failure: the audit trail exists to describe what happened, not to
gate it.

## Cost

The inverse failure is now possible: the operation commits and the audit write
fails, leaving an action with no record. That is the trade being made, and on
a system where the audit trail is a compliance artefact rather than an
operational one, the opposite choice would be correct.

Neither answer is free. This one is chosen so that observability cannot take
the application down.
