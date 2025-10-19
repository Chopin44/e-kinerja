<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-6 px-4">
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Detail Realisasi</h1>
                <p class="text-gray-500 mt-1 text-sm">
                    Menampilkan detail lengkap dari realisasi dan dokumen pendukungnya.
                </p>
            </div>
            <a href="{{ route('realisasi.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>

        {{-- Informasi Utama --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-3">Informasi Umum</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                <div>
                    <p class="text-gray-500">Kegiatan</p>
                    <p class="font-medium text-gray-900">
                        {{ $realisasi->kegiatan->nama ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500">Subkegiatan</p>
                    <p class="font-medium text-gray-900">
                        {{ $realisasi->subKegiatan->nama ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500">Tanggal Realisasi</p>
                    <p class="font-medium text-gray-900">
                        {{ \Carbon\Carbon::parse($realisasi->tanggal_realisasi)->translatedFormat('d F Y') }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500">Realisasi Fisik</p>
                    <p class="font-medium text-gray-900">
                        {{ $realisasi->realisasi_fisik ? $realisasi->realisasi_fisik.'%' : '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500">Lokasi</p>
                    <p class="font-medium text-gray-900">
                        {{ $realisasi->lokasi ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500">Catatan</p>
                    <p class="font-medium text-gray-900 whitespace-pre-line">
                        {{ $realisasi->catatan ?? '-' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Tabel Rincian --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Rincian Realisasi</h2>

            @if($realisasi->realisasiRincians->count())
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border">
                    <thead class="bg-gray-50 text-gray-700 uppercase tracking-wide text-xs">
                        <tr>
                            <th class="text-left px-4 py-2 border">Uraian</th>
                            <th class="text-left px-4 py-2 border">Realisasi Anggaran</th>
                            <th class="text-left px-4 py-2 border">Fisik (%)</th>
                            <th class="text-left px-4 py-2 border">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($realisasi->realisasiRincians as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border">{{ $r->rincianKegiatan->uraian ?? '-' }}</td>
                            <td class="px-4 py-2 border">Rp {{ number_format($r->realisasi_anggaran, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2 border">{{ $r->realisasi_fisik ?? '-' }}</td>
                            <td class="px-4 py-2 border">
                                {{ optional($r->tanggal_realisasi)->format('d/m/Y') ?? '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-semibold">
                        <tr>
                            <td class="px-4 py-2 border text-right" colspan="1">Total</td>
                            <td class="px-4 py-2 border text-left" colspan="4">
                                Rp {{ number_format($realisasi->realisasiRincians->sum('realisasi_anggaran'), 0, ',',
                                '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <p class="text-gray-500 text-sm italic">Belum ada rincian realisasi.</p>
            @endif
        </div>

        {{-- Dokumen --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Dokumen Pendukung</h2>

            @if($realisasi->dokumens->count())
            <p class="text-sm text-gray-600 mb-3">
                Total Dokumen: <b>{{ $realisasi->dokumens->count() }}</b>
            </p>

            <ul class="grid sm:grid-cols-2 gap-4">
                @foreach($realisasi->dokumens as $doc)
                <li class="border rounded-lg p-3 flex items-center gap-3 bg-gray-50 hover:bg-gray-100 transition">
                    @if(Str::endsWith(strtolower($doc->path), ['jpg', 'jpeg', 'png']))
                    {{-- Preview gambar --}}
                    <img src="{{ asset('storage/'.$doc->path) }}" alt="Dokumen"
                        class="w-16 h-16 object-cover rounded-md border">
                    @else
                    {{-- Ikon file --}}
                    <div class="w-16 h-16 flex items-center justify-center rounded-md bg-gray-200 text-gray-600">
                        <i class="fas fa-file text-2xl"></i>
                    </div>
                    @endif

                    <div class="flex-1">
                        <a href="{{ asset('storage/'.$doc->path) }}" target="_blank"
                            class="font-medium text-blue-600 hover:underline">
                            {{ $doc->nama_file }}
                        </a>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ strtoupper(pathinfo($doc->nama_file, PATHINFO_EXTENSION)) }} •
                            {{ round($doc->ukuran / 1024, 1) }} KB
                        </p>
                    </div>
                </li>
                @endforeach
            </ul>
            @else
            <p class="text-sm text-gray-500 italic">Belum ada dokumen yang diunggah.</p>
            @endif
        </div>
    </div>
</x-app-layout>