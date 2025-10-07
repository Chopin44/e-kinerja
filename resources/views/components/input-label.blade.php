@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-sm text-blue-700 dark:text-blue-300 tracking-wide'])
    }}>
    {{ $value ?? $slot }}
</label>