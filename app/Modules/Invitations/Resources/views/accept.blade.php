@extends('layouts.app')

@section('title', 'Accept your invitation')

@section('content')
    <div class="mx-auto mt-12 max-w-sm">
        <h1 class="mb-1 text-xl font-semibold tracking-tight">Accept your invitation</h1>
        <p class="mb-6 text-sm text-neutral-500">Choose a name and a password to finish signing up.</p>

        <form method="POST" action="{{ route('invitations.accept.submit') }}"
              class="rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <x-field name="name" label="Name" required/>
            <x-field name="password" label="Password" type="password" required/>

            <button type="submit"
                    class="w-full rounded-md bg-neutral-900 px-4 py-2 text-sm font-medium text-white hover:bg-neutral-800
                           dark:bg-neutral-100 dark:text-neutral-900">
                Create my account
            </button>
        </form>
    </div>
@endsection
