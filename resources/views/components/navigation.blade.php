@php
    use App\Core\Navigation\Navigation;

    // The shell asks; it does not know. Entries come from whichever modules
    // are installed, already filtered by route existence and permission.
    $links = app(Navigation::class)->visibleTo(auth()->user());
@endphp

<header class="border-b border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900">
    <div class="mx-auto flex w-full max-w-5xl items-center justify-between gap-6 px-4 py-3 sm:px-6">
        <nav class="flex items-center gap-1">
            <span class="mr-3 text-sm font-semibold tracking-tight">Modularity</span>

            @foreach ($links as $link)
                @php $active = request()->routeIs($link->route) || request()->routeIs(str_replace('.index', '.*', $link->route)); @endphp
                <a href="{{ route($link->route) }}"
                   class="rounded-md px-3 py-1.5 text-sm font-medium transition
                          {{ $active
                             ? 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900'
                             : 'text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-800' }}">
                    {{ $link->label }}
                </a>
            @endforeach
        </nav>

        <div class="flex items-center gap-3">
            <span class="hidden text-sm text-neutral-500 sm:inline">{{ auth()->user()?->getAttribute('email') }}</span>

            @if (Route::has('web.logout'))
                <form method="POST" action="{{ route('web.logout') }}">
                    @csrf
                    <button type="submit"
                            class="rounded-md border border-neutral-300 px-3 py-1.5 text-sm font-medium text-neutral-700
                                   hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-800">
                        Sign out
                    </button>
                </form>
            @endif
        </div>
    </div>
</header>
