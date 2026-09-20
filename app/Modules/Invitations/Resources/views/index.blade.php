@extends('layouts.app')

@section('title', 'Invitations')

@section('content')
    <x-page title="Invitations" description="People who have been invited but have not signed up yet."/>

    @if (session('invite_url'))
        <x-flash tone="success">
            <p class="mb-2 font-medium">Send this link on. It is shown once.</p>
            <code class="block break-all rounded bg-white/60 px-2 py-1 font-mono text-xs dark:bg-black/30">{{ session('invite_url') }}</code>
        </x-flash>
    @endif

    @can('invitations.send')
        <form method="POST" action="{{ route('invitations.store') }}"
              class="mb-6 flex flex-wrap items-end gap-3 rounded-lg border border-neutral-200 bg-white p-5 dark:border-neutral-800 dark:bg-neutral-900">
            @csrf
            <div class="w-64"><x-field name="email" label="Email" type="email" required/></div>
            <div class="w-40"><x-field name="valid_for_days" label="Valid for days" type="number" value="7"/></div>
            <button type="submit"
                    class="mb-4 rounded-md bg-neutral-900 px-4 py-2 text-sm font-medium text-white hover:bg-neutral-800
                           dark:bg-neutral-100 dark:text-neutral-900">
                Invite
            </button>
        </form>
    @endcan

    <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900">
        <table class="w-full text-sm">
            <thead class="border-b border-neutral-200 text-left text-xs uppercase tracking-wide text-neutral-500 dark:border-neutral-800">
                <tr>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Expires</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invitations as $invitation)
                    <tr class="border-b border-neutral-100 last:border-0 dark:border-neutral-800">
                        <td class="px-4 py-2 font-medium">{{ $invitation['email'] }}</td>
                        <td class="px-4 py-2 text-neutral-500">
                            {{ $invitation['expires_at'] }}
                            @if ($invitation['expired'])
                                <span class="ml-1 rounded bg-neutral-200 px-1.5 py-0.5 text-xs dark:bg-neutral-800">expired</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right">
                            @can('invitations.send')
                                <form method="POST" action="{{ route('invitations.destroy', $invitation['id']) }}"
                                      data-confirm="Revoke the invitation for {{ $invitation['email'] }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-red-600 hover:underline">Revoke</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-neutral-500">Nobody is waiting.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
