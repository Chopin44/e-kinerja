<!-- resources/views/dashboard/index.blade.php -->
<x-app-layout>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-chart-line text-blue-600 mr-3"></i>
                Dashboard Overview
            </h1>
            <p class="text-gray-600 mt-1">
                Ringkasan kegiatan Dinas Kepemudaan dan Olahraga dan Pariwisata Kabupaten Pekalongan
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Kegiatan</p>
                        <p class="text-3xl font-bold">{{ $totalKegiatan }}</p>
                    </div>
                    <i class="fas fa-tasks text-4xl text-blue-200"></i>
                </div>
            </div>

            <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-indigo-100 text-sm font-medium">Total Subkegiatan</p>
                        <p class="text-3xl font-bold">{{ $totalSubKegiatan }}</p>
                    </div>
                    <i class="fas fa-sitemap text-4xl text-indigo-200"></i>
                </div>
            </div>

            <div class="bg-gradient-to-r from-cyan-500 to-cyan-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-cyan-100 text-sm font-medium">Total Rincian</p>
                        <p class="text-3xl font-bold">{{ $totalRincian }}</p>
                    </div>
                    <i class="fas fa-list-ul text-4xl text-cyan-200"></i>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Rata-rata Fisik</p>
                        <p class="text-3xl font-bold">{{ number_format($avgProgressFisik, 1) }}%</p>
                    </div>
                    <i class="fas fa-chart-pie text-4xl text-green-200"></i>
                </div>
            </div>

            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100 text-sm font-medium">Rata-rata Anggaran</p>
                        <p class="text-3xl font-bold">{{ number_format($avgProgressAnggaran, 1) }}%</p>
                    </div>
                    <i class="fas fa-money-bill text-4xl text-yellow-200"></i>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Kegiatan On Track</p>
                        <p class="text-3xl font-bold">{{ $kegiatanOnTrack }}</p>
                    </div>
                    <i class="fas fa-check-circle text-4xl text-purple-200"></i>
                </div>
            </div>
        </div>

        <!-- Diagram Total Pagu vs Realisasi -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Total Anggaran vs Realisasi</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Dari total pagu
                    <b>Rp {{ number_format($totalPagu, 0, ',', '.') }}</b>,
                    sudah terealisasi
                    <b>Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</b>
                    ({{ number_format($persentaseRealisasi, 1) }}%)
                </p>
            </div>

            <div class="flex justify-center items-center">
                <div class="relative w-44 h-44 sm:w-56 sm:h-56 md:w-64 md:h-64">
                    <canvas id="anggaranPieChart" data-total-pagu="{{ $totalPagu }}"
                        data-total-realisasi="{{ $totalRealisasi }}" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>

        <!-- Capaian per Bidang -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Capaian per Bidang</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($bidangs as $bidang)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center mb-4">
                            <div
                                class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center text-white">
                                <i class="{{ $bidang['icon'] }}"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-gray-900">{{ $bidang['nama'] }}</h3>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $bidang['total_kegiatan'] }}</div>
                                <div class="text-xs text-gray-500">Total Kegiatan</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ number_format($bidang['avg_progress'],
                                    0) }}%</div>
                                <div class="text-xs text-gray-500">Rata-rata Anggaran</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Kegiatan Terbaru -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Kegiatan Terbaru</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Kegiatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Bidang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Progress Fisik</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Progress Anggaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($kegiatanTerbaru as $kegiatan)
                        @php
                        $budgetProgress = $kegiatan->target_anggaran > 0
                        ? ($kegiatan->current_budget_realization / $kegiatan->target_anggaran) * 100
                        : 0;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $kegiatan->nama }}</div>
                                <div class="text-sm text-gray-500">Staf Admin: {{ $kegiatan->user->name ?? '-' }}</div>
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                // jika kamu punya kolom status_evaluasi di tabel kegiatan, pakai; kalau tidak,
                                // tampilkan '-' saja.
                                $status = $kegiatan->status_evaluasi ?? '-';
                                $statusClass = 'status-' . str_replace('_', '-', $status);
                                @endphp
                                <span class="status-badge {{ $statusClass }}">{{ is_string($status) ?
                                    ucfirst(str_replace('_', ' ', $status)) : '-' }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pie Chart (gunakan Chart.js; pastikan sudah include Chart.js di layout) --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('anggaranPieChart');
            if (!canvas || !window.Chart) return;

            const totalPagu      = Number(canvas.dataset.totalPagu || 0);
            const totalRealisasi = Number(canvas.dataset.totalRealisasi || 0);
            const sisa           = Math.max(totalPagu - totalRealisasi, 0);

            new Chart(canvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Realisasi', 'Sisa'],
                    datasets: [{
                        data: [totalRealisasi, sisa],
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '65%',
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const val = ctx.parsed || 0;
                                    return ctx.label + ': Rp ' + val.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>