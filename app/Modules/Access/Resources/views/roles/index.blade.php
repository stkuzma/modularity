@extends('layouts.app')

@section('title', 'Roles')

@section('content')
    <x-page title="Roles" description="Permissions are granted through roles, never directly.">
        <x-slot:actions>
            @can('access.roles.manage')
                <a href="{{ route('roles.create') }}"
                   class="rounded-md bg-neutral-900 px-3 py-2 text-sm font-medium text-white hover:bg-neutral-800
                          dark:bg-neutral-100 dark:text-neutral-900">
                    New role
                </a>
            @endcan
        </x-slot:actions>
    </x-page>

    <div class="space-y-3">
        @forelse ($roles as $role)
            <div class="rounded-lg border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900">
                <div class="mb-2 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-medium">
                            {{ $role['label'] }}
                            @if ($role['is_system'])
                                <span class="ml-1 rounded bg-neutral-200 px-1.5 py-0.5 text-xs font-normal dark:bg-neutral-800">system</span>
                            @endif
                        </h2>
                        <p class="font-mono text-xs text-neutral-500">{{ $role['name'] }}</p>
                    </div>

                    @can('access.roles.manage')
                        @unless ($role['is_system'])
                            <div class="flex items-center gap-3">
                                <a href="{{ route('roles.edit', $role['id']) }}" class="text-xs font-medium hover:underline">Edit</a>
                                <form method="POST" action="{{ route('roles.destroy', $role['id']) }}"
                                      data-confirm="Delete {{ $role['label'] }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-red-600 hover:underline">Delete</button>
                                </form>
                            </div>
                        @endunless
                    @endcan
                </div>

                @if ($role['permissions'] === [])
                    <p class="text-xs text-neutral-500">No permissions.</p>
                @else
                    <ul class="flex flex-wrap gap-1.5">
                        @foreach ($role['permissions'] as $permission)
                            <li class="rounded bg-neutral-100 px-2 py-0.5 font-mono text-xs dark:bg-neutral-800">{{ $permission }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @empty
            <p class="text-sm text-neutral-500">No roles yet.</p>
        @endforelse
    </div>
@endsection
