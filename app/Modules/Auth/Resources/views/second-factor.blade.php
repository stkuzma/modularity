@extends('layouts.app')

@section('title', 'Two-factor authentication')

@section('content')
    <div class="mx-auto mt-12 max-w-sm">
        <h1 class="mb-1 text-xl font-semibold tracking-tight">Two-factor authentication</h1>
        <p class="mb-6 text-sm text-neutral-500">
            Enter the six-digit code from your authenticator app, or one of your recovery codes.
        </p>

        <form method="POST" action="{{ route('second-factor.submit') }}"
              class="rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
            @csrf

            <x-field name="code" label="Code" required/>

            <button type="submit"
                    class="w-full rounded-md bg-neutral-900 px-4 py-2 text-sm font-medium text-white
                           hover:bg-neutral-800 dark:bg-neutral-100 dark:text-neutral-900 dark:hover:bg-white">
                Continue
            </button>
        </form>
    </div>
@endsection
