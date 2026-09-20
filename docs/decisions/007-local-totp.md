# 007. TOTP written out

## Context

The `Auth` module needs time-based one-time passwords. The obvious answer is a
maintained library.

## Decision

RFC 6238 is implemented in this repository, in about eighty lines, and checked
against the test vectors published in the RFC itself.

## Why here

This is a reference repository, and the acceptance window, the digit count and
the drift tolerance are the interesting parts. Keeping them visible and
testable is worth more here than one fewer file. The RFC vectors mean the
implementation is verifiable against the specification rather than against its
author.

## Why I would not do this in production

Cryptographic code is a poor place to own maintenance. A maintained library
gets scrutiny, advisories and fixes that a hand-rolled implementation in an
application repository does not. In a production system I would evaluate a
maintained security library and keep the tests, not the implementation.

That is the honest version of this decision, and the reason it is recorded
rather than presented as a straightforward improvement.

## The QR code is a library

Enrolment renders the `otpauth://` URI as an inline SVG using
`bacon/bacon-qr-code`. The contrast with the decision above is the point: a QR
code is a data encoding with no decision in it, so writing one would buy
nothing and cost a maintenance burden. TOTP has an acceptance window, a drift
tolerance and a digit count worth making visible. That is the line.
