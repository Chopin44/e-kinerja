<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-blue-400">
        <div>
            <a href="/">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </a>
        </div>

        <div class="mt-6 text-center max-w-sm mx-auto leading-tight">
            <h1 class="text-4xl font-extrabold text-blue-700 dark:text-white">
                E-Kinerja
            </h1>
            <p class="mt-2 text-base text-gray-700 dark:text-blue-100 font-medium">
                Dinas Kepemudaan, Olahraga, dan Pariwisata<br>
                Kabupaten Pekalongan
            </p>
        </div>



        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-white shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
</body>

</html>