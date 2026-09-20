@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
    <div class="mx-auto mt-12 max-w-sm">
        <h1 class="mb-1 text-xl font-semibold tracking-tight">Sign in</h1>
        <p class="mb-6 text-sm text-neutral-500">Modularity</p>

        <form method="POST" action="{{ route('sign-in.submit') }}"
              class="rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
            @csrf

            <x-field name="email" label="Email" type="email" required/>
            <x-field name="password" label="Password" type="password" required/>

            <label class="mb-5 flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" value="1" class="rounded border-neutral-300">
                Remember me
            </label>

            <button type="submit"
                    class="w-full rounded-md bg-neutral-900 px-4 py-2 text-sm font-medium text-white
                           hover:bg-neutral-800 dark:bg-neutral-100 dark:text-neutral-900 dark:hover:bg-white">
                Sign in
            </button>
        </form>
    </div>
@endsection
