@extends('layouts.app')

@section('title', 'Security')

@section('content')
    <x-page title="Security" description="Your second factor and your API tokens."/>

    @if (session('issued_token'))
        <x-flash tone="success">
            <p class="mb-2 font-medium">Copy this token now. It is not shown again.</p>
            <code class="block break-all rounded bg-white/60 px-2 py-1 font-mono text-xs dark:bg-black/30">{{ session('issued_token') }}</code>
        </x-flash>
    @endif

    <section class="mb-6 rounded-lg border border-neutral-200 bg-white p-5 dark:border-neutral-800 dark:bg-neutral-900">
        <h2 class="mb-1 text-sm font-semibold uppercase tracking-wide text-neutral-500">Two-factor authentication</h2>

        @if ($confirmed)
            <p class="mb-4 text-sm">Enabled. Turning it off requires a current code.</p>

            <form method="POST" action="{{ route('security.second-factor.disable') }}"
                  data-confirm="Turn off two-factor authentication?" class="flex flex-wrap items-end gap-3">
                @csrf
                @method('DELETE')
                <div class="w-40"><x-field name="code" label="Current code" required/></div>
                <button type="submit"
                        class="mb-4 rounded-md border border-red-300 px-3 py-2 text-sm font-medium text-red-700
                               hover:bg-red-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950">
                    Turn off
                </button>
            </form>
        @elseif (session('mfa_secret'))
            <p class="mb-3 text-sm">
                Scan this with your authenticator app, then confirm with the code it shows.
            </p>

            <div class="mb-3 flex flex-wrap items-start gap-5">
                <div class="rounded-lg bg-white p-2">{!! session('mfa_qr') !!}</div>

                <div class="min-w-48 flex-1">
                    <p class="mb-1 text-xs text-neutral-500">Or enter the key by hand:</p>
                    <code class="block break-all rounded bg-neutral-100 px-3 py-2 font-mono text-xs dark:bg-neutral-800">{{ session('mfa_secret') }}</code>
                </div>
            </div>

            <details class="mb-4">
                <summary class="cursor-pointer text-sm text-neutral-500">Recovery codes, shown once</summary>
                <ul class="mt-2 grid grid-cols-2 gap-1 font-mono text-xs">
                    @foreach (session('mfa_recovery_codes', []) as $code)
                        <li class="rounded bg-neutral-100 px-2 py-1 dark:bg-neutral-800">{{ $code }}</li>
                    @endforeach
                </ul>
            </details>

            <form method="POST" action="{{ route('security.second-factor.confirm') }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="w-40"><x-field name="code" label="Code from the app" required/></div>
                <button type="submit"
                        class="mb-4 rounded-md bg-neutral-900 px-3 py-2 text-sm font-medium text-white
                               hover:bg-neutral-800 dark:bg-neutral-100 dark:text-neutral-900">
                    Confirm
                </button>
            </form>
        @else
            <p class="mb-4 text-sm text-neutral-500">Not enrolled.</p>

            <form method="POST" action="{{ route('security.second-factor.enrol') }}">
                @csrf
                <button type="submit"
                        class="rounded-md bg-neutral-900 px-3 py-2 text-sm font-medium text-white
                               hover:bg-neutral-800 dark:bg-neutral-100 dark:text-neutral-900">
                    Set up
                </button>
            </form>
        @endif
    </section>

    <section class="rounded-lg border border-neutral-200 bg-white p-5 dark:border-neutral-800 dark:bg-neutral-900">
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-neutral-500">API tokens</h2>

        @if ($tokens === [])
            <p class="mb-4 text-sm text-neutral-500">No tokens.</p>
        @else
            <table class="mb-5 w-full text-sm">
                <thead class="border-b border-neutral-200 text-left text-xs uppercase tracking-wide text-neutral-500 dark:border-neutral-800">
                    <tr>
                        <th class="py-2">Name</th>
                        <th class="py-2">Last used</th>
                        <th class="py-2">Expires</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tokens as $token)
                        <tr class="border-b border-neutral-100 dark:border-neutral-800">
                            <td class="py-2 font-medium">{{ $token['name'] }}</td>
                            <td class="py-2 text-neutral-500">{{ $token['last_used_at'] ?? 'never' }}</td>
                            <td class="py-2 text-neutral-500">{{ $token['expires_at'] ?? 'no expiry' }}</td>
                            <td class="py-2 text-right">
                                <form method="POST" action="{{ route('security.tokens.destroy', $token['id']) }}"
                                      data-confirm="Revoke {{ $token['name'] }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-red-600 hover:underline">Revoke</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <form method="POST" action="{{ route('security.tokens.store') }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <div class="w-56"><x-field name="name" label="New token name" required/></div>
            <div class="w-40"><x-field name="expires_in_days" label="Expires in days" type="number" hint="Optional"/></div>
            <button type="submit"
                    class="mb-4 rounded-md bg-neutral-900 px-3 py-2 text-sm font-medium text-white
                           hover:bg-neutral-800 dark:bg-neutral-100 dark:text-neutral-900">
                Create
            </button>
        </form>
    </section>
@endsection
