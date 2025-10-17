<x-app-layout>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            border-radius: 9999px;
            background: #e5e7eb;
            overflow: hidden
        }

        .progress-fill {
            height: 8px;
            border-radius: 9999px;
            background: #10b981
        }

        .progress-fill.budget {
            background: #3b82f6
        }
    </style>

    <div class="space-y-6">
        <!-- Header -->
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
                    <i class="fas fa-plus mr-2"></i> Tambah Kegiatan
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                {{-- BIDANG --}}
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

                {{-- TAHUN --}}
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

                {{-- STATUS --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status')=='draft' ? 'selected' : '' }}>Draft</option>
                        <option value="aktif" {{ request('status')=='aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="selesai" {{ request('status')=='selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>

                {{-- BTN --}}
                <div>
                    <button type="submit"
                        class="w-full md:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-search mr-2"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Kegiatan Table (tanpa progress di level kegiatan) -->
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
                                Status</th>
                            <th class="px-3 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($kegiatans as $kegiatan)
                        {{-- Satu KEGIATAN = satu
                    <tbody> supaya 2 baris (utama+expand) berbagi scope Alpine --}}
                    <tbody x-data="{ open: false }">
                        <tr class="hover:bg-gray-50">
                            <!-- Kegiatan -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $kegiatan->nama }}</div>
                                <div class="text-xs text-gray-500">Periode: {{ $kegiatan->periode_type }} • Tahun: {{
                                    $kegiatan->tahun }}</div>
                            </td>

                            <!-- Bidang -->
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $kegiatan->bidang->nama }}
                            </td>

                            <!-- Status -->
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

                            <!-- Aksi -->
                            <td class="px-6 py-4 text-sm">
                                <div class="flex items-center space-x-2">
                                    <button type="button" @click="open = !open"
                                        class="btn-secondary text-xs px-3 py-1 inline-flex items-center">
                                        <i class="fas fa-layer-group mr-1"></i> Sub & Rincian
                                    </button>

                                    <a href="{{ route('kegiatan.show', $kegiatan) }}"
                                        class="btn-primary text-xs px-3 py-1 inline-flex items-center">
                                        <i class="fas fa-eye mr-1"></i> Detail
                                    </a>

                                    @can('update', $kegiatan)
                                    <a href="{{ route('kegiatan.edit', $kegiatan) }}"
                                        class="btn-secondary text-xs px-3 py-1 inline-flex items-center">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                    @endcan

                                    @can('delete', $kegiatan)
                                    <form action="{{ route('kegiatan.destroy', $kegiatan) }}" method="POST"
                                        class="inline-flex items-center"
                                        data-confirm="Menghapus kegiatan <b>{{ $kegiatan->nama }}</b> akan menghapus data terkait. Lanjutkan?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn-danger text-xs px-3 py-1 inline-flex items-center">
                                            <i class="fas fa-trash-alt mr-1"></i> Hapus
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>

                        {{-- ROW EXPAND: Subkegiatan + Rincian --}}
                        <tr x-show="open" x-cloak>
                            <td colspan="4" class="bg-gray-50 px-6 py-4">
                                @if($kegiatan->subKegiatans->isEmpty())
                                <div class="text-sm text-gray-500">Belum ada subkegiatan.</div>
                                @else
                                <div class="text-sm text-gray-700 font-semibold mb-2">Subkegiatan</div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full border text-sm divide-y divide-gray-200">
                                        <thead class="bg-gray-100 text-gray-700">
                                            <tr>
                                                <th class="px-3 py-2 text-left">Nama Subkegiatan</th>
                                                <th class="px-3 py-2 text-left">Staf Admin</th>
                                                <th class="px-3 py-2 text-left">Target</th>
                                                <th class="px-3 py-2 text-left">Total Rincian</th>
                                                <th class="px-3 py-2 text-left">Progress</th>
                                                <th class="px-3 py-2 text-left">Rincian</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($kegiatan->subKegiatans as $sub)
                                            {{-- Satu SUB = satu
                                        <tbody> supaya baris rincian berbagi scope "detil" --}}
                                        <tbody x-data="{ detil: false }">
                                            @php
                                            $totalRincian = (float) $sub->rincianKegiatans->sum('anggaran');
                                            $target = (float) ($sub->target_anggaran ?? 0);

                                            $budgetProgress = 0; // nanti diganti realisasi sub

                                            $targetFisik = isset($sub->target_fisik) ? (float)$sub->target_fisik : 0;
                                            $realisasiFisik = isset($sub->realisasi_fisik) ?
                                            (float)$sub->realisasi_fisik : 0;
                                            $fisikProgress = $targetFisik > 0 ? ($realisasiFisik / $targetFisik) * 100 :
                                            0;
                                            $hasFisikData = ($targetFisik > 0 || $realisasiFisik > 0);
                                            @endphp

                                            <tr class="hover:bg-white">
                                                <td class="px-3 py-2 font-medium text-gray-900">{{ $sub->nama }}</td>
                                                <td class="px-3 py-2 text-gray-700">{{ $sub->user->name ?? '-' }}</td>
                                                <td class="px-3 py-2 whitespace-nowrap">Rp {{ number_format($target, 0,
                                                    ',', '.') }}</td>
                                                <td class="px-3 py-2 whitespace-nowrap">Rp {{
                                                    number_format($totalRincian, 0, ',', '.') }}</td>
                                                <td class="px-3 py-2 w-72">
                                                    <div class="space-y-2">
                                                        <div>
                                                            <div
                                                                class="flex justify-between text-xs text-gray-600 mb-1">
                                                                <span>Fisik</span>
                                                                <span>{{ number_format($hasFisikData ? $fisikProgress :
                                                                    0, 1) }}%</span>
                                                            </div>
                                                            <div class="progress-bar">
                                                                <div class="progress-fill"
                                                                    style="width: {{ max(0,min(100,$hasFisikData ? $fisikProgress : 0)) }}%">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div
                                                                class="flex justify-between text-xs text-gray-600 mb-1">
                                                                <span>Anggaran</span>
                                                                <span>{{ number_format($budgetProgress, 1) }}%</span>
                                                            </div>
                                                            <div class="progress-bar">
                                                                <div class="progress-fill budget"
                                                                    style="width: {{ max(0,min(100,$budgetProgress)) }}%">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="text-[10px] text-gray-400">
                                                            {{ $sub->periode_type ?? $kegiatan->periode_type }} • {{
                                                            $sub->tahun ?? $kegiatan->tahun }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-2">
                                                    <button type="button" @click="detil = !detil"
                                                        class="text-blue-600 hover:underline text-xs">
                                                        Lihat Rincian ({{ $sub->rincianKegiatans->count() }})
                                                    </button>
                                                </td>
                                            </tr>

                                            {{-- RINCIAN (kategori tampil DI SINI) --}}
                                            <tr x-show="detil" x-cloak>
                                                <td colspan="7" class="bg-gray-50 px-3 py-3">
                                                    @if($sub->rincianKegiatans->isEmpty())
                                                    <div class="text-xs text-gray-500">Belum ada rincian.</div>
                                                    @else
                                                    <table class="min-w-full border text-xs divide-y divide-gray-200">
                                                        <thead class="bg-gray-100 text-gray-700">
                                                            <tr>
                                                                <th class="px-2 py-1 text-left">Uraian</th>
                                                                <th class="px-2 py-1 text-left">Kategori</th>
                                                                <th class="px-2 py-1 text-left">Anggaran</th>
                                                                <th class="px-2 py-1 text-left">Satuan</th>
                                                                <th class="px-2 py-1 text-left">Volume</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($sub->rincianKegiatans as $r)
                                                            <tr>
                                                                <td class="px-2 py-1">{{ $r->uraian }}</td>
                                                                <td class="px-2 py-1">{{ $r->kategori ?
                                                                    ucwords(str_replace('_',' ',$r->kategori)) : '-' }}
                                                                </td>
                                                                <td class="px-2 py-1 whitespace-nowrap">Rp {{
                                                                    number_format($r->anggaran ?? 0, 0, ',', '.') }}
                                                                </td>
                                                                <td class="px-2 py-1">{{ $r->satuan ?? '-' }}</td>
                                                                <td class="px-2 py-1">{{ $r->volume ?? '-' }}</td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
            </td>
            </tr>
            </tbody>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-3"></i>
                    <div>Belum ada data kegiatan</div>
                    <a href="{{ route('kegiatan.create') }}" class="text-blue-600 hover:underline">Tambah kegiatan
                        pertama</a>
                </td>
            </tr>
            @endforelse
            </tbody>

            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t custom-pagination">
            {{ $kegiatans->withQueryString()->links() }}
        </div>
    </div>
    </div>
</x-app-layout>