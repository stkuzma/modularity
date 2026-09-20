@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <x-page title="Dashboard" description="What this session is and what it may do."/>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-lg border border-neutral-200 bg-white p-5 dark:border-neutral-800 dark:bg-neutral-900">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-neutral-500">Identity</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-neutral-500">Name</dt>
                    <dd class="font-medium">{{ $identity['name'] }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-neutral-500">Email</dt>
                    <dd class="font-medium">{{ $identity['email'] }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-lg border border-neutral-200 bg-white p-5 dark:border-neutral-800 dark:bg-neutral-900">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-neutral-500">Permissions</h2>

            @if ($permissions === [])
                <p class="text-sm text-neutral-500">
                    None. Permissions arrive through roles, and this account holds none.
                </p>
            @else
                <ul class="flex flex-wrap gap-1.5">
                    @foreach ($permissions as $permission)
                        <li class="rounded bg-neutral-100 px-2 py-1 font-mono text-xs dark:bg-neutral-800">{{ $permission }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
