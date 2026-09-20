@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <x-page title="Users" description="Accounts in this application.">
        <x-slot:actions>
            @can('users.create')
                <a href="{{ route('users.create') }}"
                   class="rounded-md bg-neutral-900 px-3 py-2 text-sm font-medium text-white hover:bg-neutral-800
                          dark:bg-neutral-100 dark:text-neutral-900">
                    New user
                </a>
            @endcan
        </x-slot:actions>
    </x-page>

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <div class="w-56">
            <x-field name="search" label="Search" :value="$filter->search"/>
        </div>

        <div class="w-40">
            <x-field name="status" label="Status">
                <select name="status" id="status"
                        class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm
                               dark:border-neutral-700 dark:bg-neutral-900">
                    <option value="">Any</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected($filter->status === $status)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </x-field>
        </div>

        <button type="submit"
                class="mb-4 rounded-md border border-neutral-300 px-3 py-2 text-sm font-medium
                       hover:bg-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800">
            Filter
        </button>
    </form>

    <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900">
        <table class="w-full text-sm">
            <thead class="border-b border-neutral-200 text-left text-xs uppercase tracking-wide text-neutral-500 dark:border-neutral-800">
                <tr>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="border-b border-neutral-100 last:border-0 dark:border-neutral-800">
                        <td class="px-4 py-2 font-medium">{{ $user['name'] }}</td>
                        <td class="px-4 py-2 text-neutral-500">{{ $user['email'] }}</td>
                        <td class="px-4 py-2">
                            <span class="rounded px-2 py-0.5 text-xs font-medium
                                {{ $user['status'] === 'active'
                                   ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'
                                   : 'bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300' }}">
                                {{ $user['status'] }}
                            </span>
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex items-center justify-end gap-3">
                                @can('users.update')
                                    <a href="{{ route('users.edit', $user['id']) }}" class="text-xs font-medium hover:underline">Edit</a>
                                @endcan

                                @can('users.delete')
                                    <form method="POST" action="{{ route('users.destroy', $user['id']) }}"
                                          data-confirm="Delete {{ $user['name'] }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-600 hover:underline">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-neutral-500">No users match that filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $page->links() }}</div>
@endsection
