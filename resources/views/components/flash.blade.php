@props(['tone' => 'success'])

@php
    $tones = [
        'success' => 'border-emerald-300 bg-emerald-50 text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200',
        'danger' => 'border-red-300 bg-red-50 text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-200',
    ];
@endphp

<div data-flash class="mb-6 flex items-start justify-between gap-4 rounded-lg border px-4 py-3 text-sm {{ $tones[$tone] }}">
    <div>{{ $slot }}</div>
    <button type="button" data-dismiss aria-label="Dismiss" class="opacity-60 transition hover:opacity-100">&times;</button>
</div>
