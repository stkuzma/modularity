# 003. Processor and Presenter are conventions

## Context

`Processor` and `Presenter` look like abstractions. Nothing in the application
resolves anything by either interface, and neither adds runtime behaviour.

## Decision

Keep both, and be honest about what they are: a shared vocabulary for "a use
case" and "output shaping", plus generic type parameters that static analysis
uses at call sites. `@implements Processor<CreateUserData, User>` is what lets
PHPStan check a caller at level 9.

They are conventions with teeth from tooling, not polymorphism.

## Why not delete them

`CreateUser::execute()` would read just as well, and if the generics were not
being checked that is what this would be. The generics are the reason to keep
the interface; the vocabulary is a side benefit.

## Why not Laravel API Resources instead of presenters

A Resource is a framework type that takes a `Request`. A presenter takes a
domain object and returns an array, which is what lets the same one serve a
JSON endpoint and a Blade screen. Presenters also list fields explicitly, so
adding a column never leaks it into a response.

## Cost

Two interfaces that a reader may reasonably expect to be doing more than they
do. Hence this record.
