<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: false }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SIMONIK OPD') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --primary-color: #1e3c72;
            --primary-dark: #2a5298;
        }

        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        }

        .nav-link {
            transition: all 0.3s ease;
            border-radius: 8px;
            margin-bottom: 4px;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.15) !important;
            transform: translateX(4px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.2) !important;
            border-left: 4px solid white;
            font-weight: 600;
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-50">

    <!-- Overlay (aktif di semua ukuran saat sidebarOpen true) -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-20" x-show="sidebarOpen" x-transition.opacity
        @click="sidebarOpen = false"></div>

    <div class="min-h-screen flex">

        <!-- Sidebar -->
        <aside
            class="fixed left-0 top-0 z-30 h-full w-72 sidebar shadow-xl pt-20 transform transition-transform duration-300"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex flex-col h-full px-4 py-6 text-white">
                <!-- Close btn -->
                <div class="flex justify-end mb-4">
                    <button @click="sidebarOpen = false" class="hover:text-gray-300">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Menu -->
                <nav class="flex-1 space-y-2">
                    <a href="{{ route('dashboard') }}"
                        class="nav-link block px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-20 {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-chart-line mr-3"></i> Dashboard
                    </a>

                    @can('admin')
                    <a href="{{ route('users.index') }}"
                        class="nav-link block px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-20 {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fas fa-users mr-3"></i> Kelola Pengguna
                    </a>
                    @endcan

                    <a href="{{ route('kegiatan.index') }}"
                        class="nav-link block px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-20 {{ request()->routeIs('kegiatan.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-plus mr-3"></i> Rencana Kegiatan
                    </a>

                    <a href="{{ route('realisasi.index') }}"
                        class="nav-link block px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-20 {{ request()->routeIs('realisasi.*') ? 'active' : '' }}">
                        <i class="fas fa-tasks mr-3"></i> Input Realisasi
                    </a>

                    <a href="{{ route('monitoring.index') }}"
                        class="nav-link block px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-20 {{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar mr-3"></i> Monitoring
                    </a>

                    <a href="{{ route('laporan.index') }}"
                        class="nav-link block px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-20 {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                        <i class="fas fa-file-alt mr-3"></i> Laporan Kinerja
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Konten utama -->
        <div class="flex-1 flex flex-col w-0">
            <!-- Navbar -->
            <nav class="bg-white border-b border-gray-200 fixed w-full z-30 top-0">
                <div class="px-6 lg:px-10 py-3 flex items-center justify-between">
                    <!-- Kiri: logo dan hamburger -->
                    <div class="flex items-center gap-3">
                        <!-- Hamburger -->
                        <button @click="sidebarOpen = !sidebarOpen"
                            class="text-gray-600 hover:text-gray-900 cursor-pointer p-2 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-bars text-lg"></i>
                        </button>

                        <!-- Logo -->
                        <a href="{{ route('dashboard') }}" class="flex items-center text-xl font-bold">
                            <img src="{{ asset('images/dinporapar.png') }}" alt="Logo DINPORAPAR"
                                class="w-12 h-12 mr-4">
                            <div class="flex flex-col leading-tight">
                                <span class="text-blue-600">E-Kinerja</span>
                                <span class="text-sm text-gray-800 font-normal">
                                    DINPORAPAR Kab. Pekalongan
                                </span>
                            </div>
                        </a>
                    </div>

                    <!-- Kanan: tahun & user -->
                    <div class="flex items-center gap-6">
                        <!-- Tahun aktif -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Tahun:</label>
                            <select id="yearSelector"
                                class="border border-gray-300 rounded-md px-3 py-1 text-sm min-w-[100px] focus:ring-blue-500 focus:border-blue-500">
                                @for($year = date('Y') - 2; $year <= date('Y') + 2; $year++) <option value="{{ $year }}"
                                    {{ $year==date('Y') ? 'selected' : '' }}>
                                    {{ $year }}
                                    </option>
                                    @endfor
                            </select>
                        </div>

                        <!-- User info -->
                        <div class="flex items-center gap-3">
                            <div class="text-right hidden sm:block">
                                <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-gray-500">{{ Auth::user()->bidang->nama ?? 'Admin' }}</div>
                            </div>
                            <div class="w-9 h-9 bg-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-semibold">{{ strtoupper(substr(Auth::user()->name,
                                    0, 1)) }}</span>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="text-gray-500 hover:text-red-600 transition-colors p-2 rounded-md hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>


            <!-- Main -->
            <main id="mainContent" class="pt-20 px-6">
                @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                </div>
                @endif

                @if (session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
        // Tahun aktif selector
        document.addEventListener('DOMContentLoaded', function () {
            const yearSelector = document.getElementById('yearSelector');
            if (yearSelector) {
                yearSelector.addEventListener('change', function() {
                    const currentUrl = new URL(window.location);
                    currentUrl.searchParams.set('tahun', this.value);
                    window.location.href = currentUrl.toString();
                });
            }
        });
    </script>
</body>

</html>