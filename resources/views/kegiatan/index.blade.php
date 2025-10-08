<!-- resources/views/kegiatan/index.blade.php -->
<x-app-layout>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-calendar-plus text-blue-600 mr-3"></i>
                        Rencana Kegiatan
                    </h1>
                    <p class="text-gray-600 mt-1">Kelola rencana kegiatan tahunan dan bulanan</p>
                </div>
                <a href="{{ route('kegiatan.create') }}" class="btn-primary">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Kegiatan
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bidang</label>
                    <select name="bidang_id" class="form-select">
                        <option value="">Semua Bidang</option>
                        @foreach($bidangs as $bidang)
                        <option value="{{ $bidang->id }}" {{ request('bidang_id')==$bidang->id ? 'selected' : '' }}>
                            {{ $bidang->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                    <select name="tahun" class="form-select">
                        @for($year = date('Y') - 2; $year <= date('Y') + 2; $year++) <option value="{{ $year }}" {{
                            request('tahun', date('Y'))==$year ? 'selected' : '' }}>
                            {{ $year }}
                            </option>
                            @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status')=='draft' ? 'selected' : '' }}>Draft</option>
                        <option value="aktif" {{ request('status')=='aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="selesai" {{ request('status')=='selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Kegiatan Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Kegiatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Bidang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Target</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Progress</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($kegiatans as $kegiatan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $kegiatan->nama }}</div>
                                <div class="text-sm text-gray-500">Staf Admin: {{ $kegiatan->user->name }}</div>
                                <div class="text-xs text-gray-400">{{ ucwords(str_replace('_', ' ', $kegiatan->kategori))}}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $kegiatan->bidang->nama }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div>Fisik: {{ $kegiatan->target_fisik }}%</div>
                                <div>Anggaran: </div>
                                <div class="whitespace-nowrap">Rp {{ number_format($kegiatan->target_anggaran, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-2">
                                    <div>
                                        <div class="flex justify-between text-xs text-gray-600 mb-1">
                                            <span>Fisik</span>
                                            <span>{{ number_format($kegiatan->current_progress, 1) }}%</span>
                                        </div>
                                        <div class="progress-bar">
                                            <div class="progress-fill"
                                                style="width: {{ $kegiatan->current_progress }}%"></div>
                                        </div>
                                    </div>
                                    @php
                                    $budgetProgress = $kegiatan->target_anggaran > 0 ?
                                    ($kegiatan->current_budget_realization / $kegiatan->target_anggaran) * 100 : 0;
                                    @endphp
                                    <div>
                                        <div class="flex justify-between text-xs text-gray-600 mb-1">
                                            <span>Anggaran</span>
                                            <span>{{ number_format($budgetProgress, 1) }}%</span>
                                        </div>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: {{ $budgetProgress }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                $statusClasses = [
                                'draft' => 'bg-gray-100 text-gray-800',
                                'aktif' => 'bg-green-100 text-green-800',
                                'selesai' => 'bg-blue-100 text-blue-800',
                                ];
                                @endphp
                                <span
                                    class="px-2 inline-flex text-md leading-5 font-semibold rounded-lg {{ $statusClasses[$kegiatan->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($kegiatan->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('kegiatan.show', $kegiatan) }}" class="btn-primary text-xs px-3 py-1 inline-flex items-center">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </a>

                                @can('update', $kegiatan)
                                <a href="{{ route('kegiatan.edit', $kegiatan) }}" class="btn-secondary text-xs px-3 py-1 inline-flex items-center">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                                @endcan

                                @can('delete', $kegiatan)
                                <form action="{{ route('kegiatan.destroy', $kegiatan) }}" method="POST" class="inline-flex items-center">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger text-xs px-3 py-1 inline-flex items-center"
                                        onclick="return confirm('Yakin ingin menghapus kegiatan ini?')">
                                        <i class="fas fa-trash-alt mr-1"></i> Hapus
                                    </button>
                                </form>
                                @endcan
                            </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3"></i>
                                <div>Belum ada data kegiatan</div>
                                <a href="{{ route('kegiatan.create') }}" class="text-blue-600 hover:underline">Tambah
                                    kegiatan pertama</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t">
                {{ $kegiatans->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>