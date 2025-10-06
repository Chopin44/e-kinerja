<x-app-layout>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-chart-bar text-blue-600 mr-3"></i>
                Monitoring & Evaluasi
            </h1>
            <p class="text-gray-600 mt-1">Monitor progress kegiatan dan berikan evaluasi</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Kegiatan</p>
                        <p class="text-3xl font-bold">{{ $totalKegiatan ?? 0 }}</p>
                    </div>
                    <i class="fas fa-tasks text-4xl text-blue-200"></i>
                </div>
            </div>
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">On Track</p>
                        <p class="text-3xl font-bold">{{ $onTrack ?? 0 }}</p>
                    </div>
                    <i class="fas fa-check-circle text-4xl text-green-200"></i>
                </div>
            </div>
            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100 text-sm font-medium">Terlambat</p>
                        <p class="text-3xl font-bold">{{ $late ?? 0 }}</p>
                    </div>
                    <i class="fas fa-exclamation-triangle text-4xl text-yellow-200"></i>
                </div>
            </div>
            <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100 text-sm font-medium">Bermasalah</p>
                        <p class="text-3xl font-bold">{{ $problem ?? 0 }}</p>
                    </div>
                    <i class="fas fa-times-circle text-4xl text-red-200"></i>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bidang</label>
                    <select name="bidang_id" class="form-select w-full rounded-md border-gray-300">
                        <option value="">Semua Bidang</option>
                        @foreach($bidangs as $bidang)
                        <option value="{{ $bidang->id }}" {{ request('bidang_id')==$bidang->id ? 'selected' : '' }}>
                            {{ $bidang->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                    <select name="periode" class="form-select w-full rounded-md border-gray-300">
                        <option value="">Semua Periode</option>
                        @foreach(['Q1','Q2','Q3','Q4'] as $q)
                        <option value="{{ $q }}" {{ request('periode')===$q ? 'selected' : '' }}>Triwulan {{
                            substr($q,2) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="form-select w-full rounded-md border-gray-300">
                        <option value="">Semua Status</option>
                        <option value="on_track" {{ request('status')=='on_track' ? 'selected' : '' }}>On Track</option>
                        <option value="late" {{ request('status')=='late' ? 'selected' : '' }}>Terlambat</option>
                        <option value="problem" {{ request('status')=='problem' ? 'selected' : '' }}>Bermasalah</option>
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

        <!-- Table -->
        <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Kegiatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bidang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($kegiatans as $item)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $item->nama }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $item->bidang->nama ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $item->periode ?? '-' }}</td>
                        <td class="px-6 py-4">
                            @if($item->status == 'on_track')
                            <span class="status-badge status-on-track">On Track</span>
                            @elseif($item->status == 'late')
                            <span class="status-badge status-warning">Terlambat</span>
                            @elseif($item->status == 'problem')
                            <span class="status-badge status-danger">Bermasalah</span>
                            @else
                            <span class="status-badge bg-gray-200 text-gray-700">{{ $item->status ?? '-' }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <a href="{{ route('monitoring.show', $item->id) }}"
                                class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('monitoring.edit', $item->id) }}"
                                class="text-yellow-600 hover:text-yellow-800">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('monitoring.destroy', $item->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800"
                                    onclick="return confirm('Yakin hapus data ini?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada data kegiatan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>