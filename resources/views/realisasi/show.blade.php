<x-app-layout>
    <div class="space-y-8 max-w-6xl mx-auto px-4">

        {{-- HEADER --}}
        <div class="bg-white rounded-lg shadow p-6 flex items-center justify-between border border-gray-100">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 flex items-center gap-3">
                    <i class="fas fa-clipboard-check text-blue-600"></i>
                    Detail Realisasi
                </h1>
                <p class="text-gray-600 mt-1 text-sm">Informasi lengkap realisasi kegiatan</p>
            </div>
            <a href="{{ route('realisasi.index') }}"
                class="text-sm bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>

        {{-- INFORMASI KEGIATAN --}}
        <div class="bg-white rounded-lg shadow border border-gray-100 p-6 space-y-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                <i class="fas fa-tasks text-blue-500"></i> Data Kegiatan
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Nama Kegiatan</p>
                    <p class="text-gray-800 font-medium">{{ $realisasi->kegiatan->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Bidang</p>
                    <p class="text-gray-800 font-medium">{{ $realisasi->kegiatan->bidang->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Penanggung Jawab</p>
                    <p class="text-gray-800 font-medium">{{ $realisasi->kegiatan->user->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Tahun</p>
                    <p class="text-gray-800 font-medium">{{ $realisasi->kegiatan->tahun ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- DETAIL REALISASI --}}
        <div class="bg-white rounded-lg shadow border border-gray-100 p-6 space-y-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                <i class="fas fa-chart-line text-green-500"></i> Data Realisasi
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Tanggal Realisasi</p>
                    <p class="text-gray-800 font-medium">
                        {{ $realisasi->tanggal_realisasi?->format('d M Y') ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Realisasi Fisik</p>
                    <div class="flex items-center gap-3">
                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full"
                                style="width: {{ $realisasi->realisasi_fisik ?? 0 }}%"></div>
                        </div>
                        <span class="text-sm font-semibold text-gray-700">{{ $realisasi->realisasi_fisik }}%</span>
                    </div>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Realisasi Anggaran</p>
                    <p class="text-gray-800 font-medium">
                        Rp {{ number_format($realisasi->realisasi_anggaran ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500">
                        Target: Rp {{ number_format($realisasi->kegiatan->target_anggaran ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Status</p>
                    @php
                    $statusColors = [
                    'draft' => 'bg-gray-100 text-gray-800',
                    'submitted' => 'bg-blue-100 text-blue-800',
                    'approved' => 'bg-green-100 text-green-800',
                    'rejected' => 'bg-red-100 text-red-800',
                    ];
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-semibold 
                        {{ $statusColors[$realisasi->status] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ ucfirst($realisasi->status) }}
                    </span>
                </div>
            </div>

            <div>
                <p class="text-xs text-gray-500 uppercase">Catatan</p>
                <div class="bg-gray-50 border rounded-lg p-3 text-sm text-gray-800">
                    {{ $realisasi->catatan ?? 'Tidak ada catatan tambahan.' }}
                </div>
            </div>
        </div>

        {{-- DOKUMEN TERLAMPIR --}}
        <div class="bg-white rounded-lg shadow border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <i class="fas fa-folder-open text-yellow-500"></i> Dokumen Lampiran
            </h3>
            @if($realisasi->dokumens->count())
            <ul class="divide-y divide-gray-200 text-sm">
                @foreach($realisasi->dokumens as $dokumen)
                <li class="flex justify-between items-center py-2">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-file-pdf text-red-500"></i>
                        <span>{{ $dokumen->nama_file }}</span>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('realisasi.preview', [$realisasi, $dokumen]) }}"
                            class="text-blue-600 hover:underline text-xs">
                            <i class="fas fa-eye mr-1"></i>Preview
                        </a>
                        <a href="{{ route('realisasi.download', [$realisasi, $dokumen]) }}"
                            class="text-green-600 hover:underline text-xs">
                            <i class="fas fa-download mr-1"></i>Download
                        </a>
                    </div>
                </li>
                @endforeach
            </ul>
            @else
            <p class="text-sm text-gray-500">Belum ada dokumen terlampir.</p>
            @endif
        </div>
    </div>
</x-app-layout>