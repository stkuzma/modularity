@extends('layouts.app')

@section('title', $role ? 'Edit role' : 'New role')

@section('content')
    <x-page :title="$role ? 'Edit role' : 'New role'"
            description="Permissions are grouped by the module that declares them."/>

    <form method="POST"
          action="{{ $role ? route('roles.update', $role['id']) : route('roles.store') }}"
          class="max-w-2xl rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
        @csrf
        @if ($role) @method('PUT') @endif

        <div class="grid gap-x-4 sm:grid-cols-2">
            <x-field name="name" label="Name" :value="$role['name'] ?? null" required hint="Lowercase slug, e.g. support-lead."/>
            <x-field name="label" label="Label" :value="$role['label'] ?? null" required/>
        </div>

        @if ($role)
            <fieldset class="mt-2">
                <legend class="mb-2 text-sm font-medium">Permissions</legend>

                @foreach ($catalogue as $module => $permissions)
                    <div class="mb-4">
                        <p class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-neutral-500">{{ $module }}</p>
                        <div class="space-y-1.5">
                            @foreach ($permissions as $name => $description)
                                <label class="flex items-start gap-2 text-sm">
                                    <input type="checkbox" name="permissions[]" value="{{ $name }}"
                                           @checked(in_array($name, old('permissions', $role['permissions']), true))
                                           class="mt-0.5 rounded border-neutral-300">
                                    <span>
                                        <span class="font-mono text-xs">{{ $name }}</span>
                                        <span class="block text-xs text-neutral-500">{{ $description }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </fieldset>
        @else
            <p class="mb-4 text-sm text-neutral-500">Permissions can be granted once the role exists.</p>
        @endif

        <div class="mt-2 flex items-center gap-3">
            <button type="submit"
                    class="rounded-md bg-neutral-900 px-4 py-2 text-sm font-medium text-white hover:bg-neutral-800
                           dark:bg-neutral-100 dark:text-neutral-900">
                {{ $role ? 'Save' : 'Create' }}
            </button>
            <a href="{{ route('roles.index') }}" class="text-sm text-neutral-500 hover:underline">Cancel</a>
        </div>
    </form>
@endsection
