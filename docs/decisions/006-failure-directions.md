# 006. Authorization fails closed, sign-in fails open

## Context

The core states two questions about the person calling and lets a module
answer them: `AccessChecker` ("may this actor do this") and `SignInGuard`
("may this account sign in at all"). Both need a default for when no module
answers.

## Decision

The defaults point in opposite directions, deliberately.

| Contract | Default | Direction |
|---|---|---|
| `AccessChecker` | `DeniesEverything` | closed |
| `SignInGuard` | `AllowsAnyAccount` | open |

## Why the asymmetry

Authorization failing open hands out capabilities nobody granted, which is the
worst outcome available. Sign-in failing closed locks every account out of an
application that may never have had account states to begin with, which is an
outage caused by a module not being installed.

The rule is: default to the answer that is wrong in the cheaper direction.

## Cost

Someone reading only one of the two will find the other surprising, which is
why this is written down rather than left as a pattern to infer.
