@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
'class' => '
w-full border border-blue-300 bg-blue-50/30 text-gray-900
rounded-lg px-4 py-2 placeholder-gray-400
focus:border-blue-500 focus:ring-2 focus:ring-blue-400 focus:outline-none
dark:bg-gray-800 dark:border-blue-700 dark:text-gray-100 dark:focus:ring-blue-400
transition duration-150 ease-in-out
'
]) !!}
/>