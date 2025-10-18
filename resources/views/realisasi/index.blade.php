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
                    <p class="text-gray-600 mt-1">Kelola realisasi fisik & anggaran subkegiatan</p>
                </div>
                <a href="{{ route('realisasi.create') }}" class="btn-primary">
                    <i class="fas fa-plus mr-2"></i>
                    Input Realisasi
                </a>
            </div>
        </div>

        <!-- Filters (tetap seperti punyamu) -->
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
                            <i class="fas fa-lock text-gray-400 mr-1"></i> Bidang Anda telah dikunci otomatis.
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
                        <option value="submitted" {{ request('status')=='submitted' ? 'selected' : '' }}>Submitted
                        </option>
                        <option value="approved" {{ request('status')=='approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

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
                                Kegiatan & Sub</th>
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
                    @forelse($realisasis as $realisasi)
                    <tbody x-data="{open:false}" class="bg-white divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50 align-top">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $realisasi->kegiatan->nama }}</div>
                                <div class="text-sm text-gray-500">{{ $realisasi->kegiatan->bidang->nama }}</div>

                                @if($realisasi->subKegiatan)
                                <div
                                    class="mt-1 inline-flex items-center px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-xs">
                                    Sub: {{ $realisasi->subKegiatan->nama }}
                                </div>
                                @endif

                                <div class="text-xs text-gray-400 mt-1">Input oleh: {{ $realisasi->user->name }}</div>

                                <div class="mt-2 text-xs text-gray-600">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100">
                                        Total rincian kegiatan: <b class="ml-1">{{ $realisasi->rincian_count ??
                                            ($realisasi->realisasiRincians->count() ?? 0) }}</b>
                                    </span>
                                    <span class="ml-2">
                                        Total anggaran rincian: <b>Rp {{
                                            number_format($realisasi->rincian_total_anggaran ??
                                            $realisasi->realisasiRincians->sum('realisasi_anggaran'), 0, ',', '.')
                                            }}</b>
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                {{ $realisasi->tanggal_realisasi->format('d/m/Y') }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-sm font-medium">{{ number_format($realisasi->realisasi_fisik, 0) }}%
                                </div>
                                <div class="progress-bar mt-1">
                                    <div class="progress-fill" style="width: {{ (float)$realisasi->realisasi_fisik }}%">
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm">
                                <div class="font-medium text-gray-900">
                                    Rp {{ number_format($realisasi->realisasi_anggaran, 0, ',', '.') }}
                                </div>
                                <div class="text-gray-500 text-xs">
                                    @php $targetSub = $realisasi->subKegiatan->target_anggaran ?? 0; @endphp
                                    Target Sub: Rp {{ number_format($targetSub, 0, ',', '.') }}
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
                                <div class="flex gap-1">
                                    <button type="button" @click="open=!open" class="text-center btn-secondary">
                                        <i class="fas fa-list mr-1"></i>
                                    </button>

                                    <a href="{{ route('realisasi.show', $realisasi) }}"
                                        class="text-center btn-primary ">
                                        <i class="fas fa-eye mr-1"></i>
                                    </a>

                                    {{-- @if($realisasi->status == 'draft|' && (Auth::id() === $realisasi->user_id ||
                                    Auth::user()->hasRole('admin|kabid'))) --}}
                                    <a href="{{ route('realisasi.edit', $realisasi) }}"
                                        class="text-center btn-secondary ">
                                        <i class="fas fa-edit mr-1 text-center"></i>
                                    </a>

                                    <form action="{{ route('realisasi.destroy', $realisasi) }}" method="POST"
                                        onsubmit="return confirm('Hapus realisasi ini beserta rincian dan dokumennya?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-danger text-center">
                                            <i class="fas fa-trash mr-1 text-center"></i>
                                        </button>
                                    </form>
                                    {{-- @endif --}}
                                </div>
                            </td>
                        </tr>

                        {{-- PANEL RINCIAN --}}
                        <tr x-show="open" x-cloak>
                            <td colspan="6" class="bg-gray-50 px-6 py-4">
                                @php $rows = $realisasi->realisasiRincians; @endphp
                                @if($rows->isEmpty())
                                <div class="text-sm text-gray-500">Belum ada realisasi rincian.</div>
                                @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full border text-xs divide-y divide-gray-200">
                                        <thead class="bg-gray-100 text-gray-700">
                                            <tr>
                                                <th class="px-3 py-2 text-left">Uraian Rincian</th>
                                                <th class="px-3 py-2 text-left">Kategori</th>
                                                <th class="px-3 py-2 text-left">Realisasi Fisik</th>
                                                <th class="px-3 py-2 text-left">Realisasi Anggaran</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach($rows as $rr)
                                            <tr class="hover:bg-white">
                                                <td class="px-3 py-2">
                                                    {{ $rr->rincianKegiatan->uraian ?? '-' }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    @php
                                                    $kat = $rr->rincianKegiatan->kategori ?? null;
                                                    @endphp
                                                    {{ $kat ? ucwords(str_replace('_',' ',$kat)) : '-' }}
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    {{ $rr->realisasi_fisik !== null ?
                                                    number_format($rr->realisasi_fisik, 2).' %' : '-' }}
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    Rp {{ number_format($rr->realisasi_anggaran ?? 0, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-gray-50">
                                            <tr class="font-semibold">
                                                <td class="px-3 py-2 text-right" colspan="3">Total Anggaran Rincian</td>
                                                <td class="px-3 py-2">
                                                    Rp {{ number_format($rows->sum('realisasi_anggaran'), 0, ',', '.')
                                                    }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                    @empty
                    <tbody>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3"></i>
                                <div>Belum ada data realisasi</div>
                                <a href="{{ route('realisasi.create') }}" class="text-blue-600 hover:underline">Input
                                    realisasi pertama</a>
                            </td>
                        </tr>
                    </tbody>
                    @endforelse
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t custom-pagination">
                {{ $realisasis->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>