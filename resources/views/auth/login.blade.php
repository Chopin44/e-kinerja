<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | E-Kinerja Dinporapar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-blue-50 to-white dark:from-gray-900 dark:to-gray-800 font-sans px-4 sm:px-6 lg:px-8">

    <!-- Header: Logo & Judul -->
    <header class="flex flex-col items-center text-center mb-8 space-y-2 animate-fade-in">
        <a href="/" class="block">
            <x-application-logo class="w-20 h-20 sm:w-24 sm:h-24 text-blue-600" />
        </a>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-blue-700 dark:text-white leading-tight">
            E-Kinerja
        </h1>
        <p class="text-sm sm:text-base text-gray-600 dark:text-blue-100 font-medium leading-snug max-w-xs sm:max-w-md">
            Dinas Kepemudaan, Olahraga, dan Pariwisata<br>
            Kabupaten Pekalongan
        </p>
    </header>

    <!-- Card Login -->
    <main
        class="w-full max-w-sm sm:max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 sm:p-8 animate-slide-up">
        <!-- Status Session -->
        @if (session('status'))
        <div class="mb-4 text-sm text-green-600 dark:text-green-400">
            {{ session('status') }}
        </div>
        @endif

        <!-- Form Login -->
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Email -->
            <div>
                <label for="email" class="block font-medium text-sm text-blue-700 dark:text-blue-300 mb-1">
                    Email
                </label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    placeholder="Masukkan email Anda" class="w-full border border-blue-300 bg-blue-50/30 text-gray-900 rounded-lg px-4 py-2 
                           placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-400 focus:outline-none
                           dark:bg-gray-800 dark:border-blue-700 dark:text-gray-100 dark:focus:ring-blue-400">
                @error('email')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block font-medium text-sm text-blue-700 dark:text-blue-300 mb-1">
                    Password
                </label>
                <input id="password" type="password" name="password" required placeholder="Masukkan password" class="w-full border border-blue-300 bg-blue-50/30 text-gray-900 rounded-lg px-4 py-2 
                           placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-400 focus:outline-none
                           dark:bg-gray-800 dark:border-blue-700 dark:text-gray-100 dark:focus:ring-blue-400">
                @error('password')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me & Lupa Password -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between text-sm gap-3 sm:gap-0">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="rounded border-blue-300 text-blue-600 focus:ring-blue-400">
                    <span class="ml-2 text-gray-700 dark:text-gray-400">Ingat saya</span>
                </label>

                @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-blue-600 hover:underline">
                    Lupa sandi?
                </a>
                @endif
            </div>

            <!-- Tombol Login -->
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg
                       focus:outline-none focus:ring-2 focus:ring-blue-400 transition duration-150 ease-in-out">
                Masuk
            </button>
        </form>

        <!-- Footer -->
        <p class="text-center text-xs text-gray-500 dark:text-gray-400 mt-8">
            &copy; {{ date('Y') }} DINPORAPAR Kabupaten Pekalongan
        </p>
    </main>

</body>

</html>