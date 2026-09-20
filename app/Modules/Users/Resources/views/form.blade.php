@extends('layouts.app')

@section('title', $user ? 'Edit user' : 'New user')

@section('content')
    <x-page :title="$user ? 'Edit user' : 'New user'"/>

    <form method="POST"
          action="{{ $user ? route('users.update', $user['id']) : route('users.store') }}"
          class="max-w-lg rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
        @csrf
        @if ($user) @method('PUT') @endif

        <x-field name="name" label="Name" :value="$user['name'] ?? null" required/>
        <x-field name="email" label="Email" type="email" :value="$user['email'] ?? null" required/>
        <x-field name="password"
                 label="Password"
                 type="password"
                 :required="! $user"
                 :hint="$user ? 'Leave blank to keep the current password.' : 'At least eight characters.'"/>

        <x-field name="status" label="Status">
            <select name="status" id="status"
                    class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm
                           dark:border-neutral-700 dark:bg-neutral-900">
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $user['status'] ?? 'active') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </x-field>

        <div class="mt-2 flex items-center gap-3">
            <button type="submit"
                    class="rounded-md bg-neutral-900 px-4 py-2 text-sm font-medium text-white hover:bg-neutral-800
                           dark:bg-neutral-100 dark:text-neutral-900">
                {{ $user ? 'Save' : 'Create' }}
            </button>
            <a href="{{ route('users.index') }}" class="text-sm text-neutral-500 hover:underline">Cancel</a>
        </div>
    </form>
@endsection
