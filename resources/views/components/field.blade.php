@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'hint' => null])

@php
    $control = 'w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm shadow-sm outline-none '
        .'focus:border-neutral-900 dark:border-neutral-700 dark:bg-neutral-900 dark:focus:border-neutral-300';
@endphp

<div class="mb-4">
    <label for="{{ $name }}" class="mb-1 block text-sm font-medium">{{ $label }}</label>

    @if (trim($slot) === '')
        <input type="{{ $type }}"
               name="{{ $name }}"
               id="{{ $name }}"
               value="{{ old($name, $value) }}"
               @if ($required) required @endif
               class="{{ $control }}">
    @else
        {{ $slot }}
    @endif

    @if ($hint)
        <p class="mt-1 text-xs text-neutral-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
