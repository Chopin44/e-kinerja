<x-app-layout>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-tasks text-blue-600 mr-3"></i>
                        Data Realisasi
                    </h1>
                    <p class="text-gray-600 mt-1">Kelola realisasi fisik dan anggaran kegiatan</p>
                </div>
                <a href="{{ route('realisasi.create') }}" class="btn-primary">
                    <i class="fas fa-plus mr-2"></i>
                    Input Realisasi
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">

                {{-- === BIDANG === --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bidang</label>

                    <div class="relative w-full">
                        <select name="bidang_id" class="form-select w-full" @unless(Auth::user()->hasRole('admin'))
                            disabled @endunless>

                            @role('admin')
                            <option value="">Semua Bidang</option>
                            @foreach($bidangs as $bidang)
                            <option value="{{ $bidang->id }}" {{ request('bidang_id')==$bidang->id ? 'selected' : '' }}>
                                {{ $bidang->nama }}
                            </option>
                            @endforeach
                            @else
                            <option value="{{ Auth::user()->bidang_id }}" selected>
                                {{ Auth::user()->bidang->nama ?? 'Tidak Ada Bidang' }}
                            </option>
                            @endrole
                        </select>

                        @unless(Auth::user()->hasRole('admin'))
                        <input type="hidden" name="bidang_id" value="{{ Auth::user()->bidang_id }}">
                        <p
                            class="text-xs text-gray-500 flex items-center mt-1.5 sm:absolute sm:top-full sm:left-0 sm:right-0 sm:mt-2">
                            <i class="fas fa-lock text-gray-400 mr-1"></i>
                            Bidang Anda telah dikunci otomatis.
                        </p>
                        @endunless
                    </div>
                </div>

                {{-- === TAHUN === --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                    <select name="tahun" class="form-select">
                        @for ($year = date('Y') - 2; $year <= date('Y') + 2; $year++) <option value="{{ $year }}" {{
                            request('tahun', date('Y'))==$year ? 'selected' : '' }}>
                            {{ $year }}
                            </option>
                            @endfor
                    </select>
                </div>

                {{-- === STATUS === --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status')=='draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ request('status')=='submitted' ? 'selected' : '' }}>Submitted
                        </option>
                        <option value="approved" {{ request('status')=='approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                {{-- === TOMBOL FILTER === --}}
                <div>
                    <button type="submit"
                        class="w-full md:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-search mr-2"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Realisasi Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Kegiatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Realisasi Fisik</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Realisasi Anggaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($realisasis as $realisasi)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $realisasi->kegiatan->nama }}</div>
                                <div class="text-sm text-gray-500">{{ $realisasi->kegiatan->bidang->nama }}</div>
                                <div class="text-xs text-gray-400">Input oleh: {{ $realisasi->user->name }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $realisasi->tanggal_realisasi->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium">{{ $realisasi->realisasi_fisik }}%</div>
                                <div class="progress-bar mt-1">
                                    <div class="progress-fill" style="width: {{ $realisasi->realisasi_fisik }}%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-medium text-gray-900">
                                    Rp {{ number_format($realisasi->realisasi_anggaran, 0, ',', '.') }}
                                </div>
                                <div class="text-gray-500 text-xs">
                                    Target: Rp {{ number_format($realisasi->kegiatan->target_anggaran, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                $statusClasses = [
                                'draft' => 'bg-gray-100 text-gray-800',
                                'submitted' => 'bg-blue-100 text-blue-800',
                                'approved' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                ];
                                @endphp
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClasses[$realisasi->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($realisasi->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm space-y-1">
                                <a href="{{ route('realisasi.show', $realisasi) }}"
                                    class="btn-primary text-xs px-3 py-1">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </a>
                                @if($realisasi->status == 'draft' && Auth::id() === $realisasi->user_id)
                                <a href="{{ route('realisasi.edit', $realisasi) }}"
                                    class="btn-secondary text-xs px-3 py-1">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3"></i>
                                <div>Belum ada data realisasi</div>
                                <a href="{{ route('realisasi.create') }}" class="text-blue-600 hover:underline">Input
                                    realisasi pertama</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t custom-pagination">
                {{ $realisasis->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>