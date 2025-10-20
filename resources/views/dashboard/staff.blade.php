<x-app-layout>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-user-circle text-blue-600 mr-3"></i>
                Dashboard Staf
            </h1>
            <p class="text-gray-600 mt-1">
                Selamat datang, <b>{{ $user->name }}</b> dari
                <b>{{ $user->bidang->nama ?? '-' }}</b> — Tahun {{ $tahun }}
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Pagu -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Anggaran</p>
                        <p class="text-3xl font-bold">Rp {{ number_format($totalPagu, 0, ',', '.') }}</p>
                    </div>
                    <i class="fas fa-money-bill-wave text-4xl text-blue-200"></i>
                </div>
            </div>

            <!-- Total Realisasi -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Total Realisasi</p>
                        <p class="text-3xl font-bold">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</p>
                    </div>
                    <i class="fas fa-coins text-4xl text-green-200"></i>
                </div>
            </div>

            <!-- Persentase Realisasi -->
            <div class="bg-gradient-to-r from-cyan-500 to-cyan-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-cyan-100 text-sm font-medium">Persentase Realisasi</p>
                        <p class="text-3xl font-bold">{{ number_format($persentaseRealisasi, 1) }}%</p>
                    </div>
                    <i class="fas fa-percentage text-4xl text-cyan-200"></i>
                </div>
            </div>

            <!-- Rata-rata Fisik -->
            <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-indigo-100 text-sm font-medium">Rata-rata Fisik</p>
                        <p class="text-3xl font-bold">{{ number_format($avgFisik, 1) }}%</p>
                    </div>
                    <i class="fas fa-chart-pie text-4xl text-indigo-200"></i>
                </div>
            </div>
        </div>

        <!-- Kegiatan Terbaru -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-clipboard-list text-blue-500 mr-2"></i>
                    Kegiatan Terbaru
                </h2>
                <span class="text-sm text-gray-500">
                    Menampilkan kegiatan terbaru yang Anda input
                </span>
            </div>

            @if($kegiatanTerbaru->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Nama Kegiatan
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Bidang
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Progress Fisik
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Realisasi Anggaran
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($kegiatanTerbaru as $kegiatan)
                        @php
                        $budgetProgress = $kegiatan->target_anggaran > 0
                        ? ($kegiatan->current_budget_realization / $kegiatan->target_anggaran) * 100
                        : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $kegiatan->nama }}</div>
                                <div class="text-sm text-gray-500">Dibuat: {{ $kegiatan->created_at->translatedFormat('d
                                    F Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $kegiatan->bidang->nama ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full progress-fill"
                                        style="width: {{ $kegiatan->current_progress }}%"></div>
                                </div>
                                <div class="text-sm text-gray-500 mt-1">{{ number_format($kegiatan->current_progress, 1)
                                    }}%</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full progress-fill"
                                        style="width: {{ $budgetProgress }}%"></div>
                                </div>
                                <div class="text-sm text-gray-500 mt-1">{{ number_format($budgetProgress, 1) }}%</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-10 text-center text-gray-500">
                <i class="fas fa-folder-open text-5xl mb-4 text-gray-400"></i>
                <p class="text-lg font-semibold">Belum ada kegiatan yang Anda input.</p>
                <p class="text-sm text-gray-500 mt-1">
                    Silakan tambahkan kegiatan baru di menu <b>Kegiatan</b>.
                </p>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>