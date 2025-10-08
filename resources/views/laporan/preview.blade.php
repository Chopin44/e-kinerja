<div class="space-y-6">
    @php
    use Carbon\Carbon;

    $periodeFormat = isset($periode)
    ? (strlen($periode) === 7
    ? Carbon::parse($periode)->translatedFormat('F Y')
    : Carbon::parse($periode . '-01')->translatedFormat('Y'))
    : now()->translatedFormat('F Y');

    // Tambahan khusus triwulan
    if ($jenis_laporan === 'triwulan') {
    $quarter = $data['quarter'] ?? ceil(Carbon::parse($periode)->month / 3);
    $map = [
    1 => 'Triwulan I (Januari – Maret)',
    2 => 'Triwulan II (April – Juni)',
    3 => 'Triwulan III (Juli – September)',
    4 => 'Triwulan IV (Oktober – Desember)',
    ];
    $periodeFormat = $map[$quarter] . ' ' . (Carbon::parse($periode)->year);
    }
    @endphp

    <div class="text-center border-b pb-4">
        <h2 class="text-2xl font-bold text-gray-900 tracking-wide uppercase">LAPORAN KINERJA</h2>
        <p class="text-gray-700 text-lg font-semibold">
            Periode: {{ $periodeFormat }}
        </p>
    </div>
    {{-- ===== KONTEN BERDASARKAN JENIS ===== --}}
    @switch($jenis_laporan)

    {{-- ======================== KINERJA BIDANG ======================== --}}
    @case('kinerja_bidang')
    <div class="overflow-x-auto">
        <table class="min-w-full border text-sm divide-y divide-gray-200">
            <thead class="bg-gray-50 text-gray-700 uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">Bidang</th>
                    <th class="px-4 py-2 text-left">Nama Kegiatan</th>
                    <th class="px-4 py-2 text-left">Realisasi Anggaran</th>
                    <th class="px-4 py-2 text-left">Deviasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['perBidang'] ?? [] as $row)
                @php
                $bidangModel = \App\Models\Bidang::where('nama', $row['nama'])->first();
                $list = $bidangModel ? $bidangModel->kegiatans()->pluck('nama')->toArray() : [];
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 font-medium">{{ $row['nama'] }}</td>
                    <td class="px-4 py-2">
                        @if(count($list))
                        <ul class="list-disc ml-4 text-gray-700 text-xs">
                            @foreach($list as $nama)
                            <li>{{ $nama }}</li>
                            @endforeach
                        </ul>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-2">Rp {{ number_format($row['realisasi_anggaran'], 0, ',', '.') }}</td>
                    <td class="px-4 py-2 {{ $row['deviasi'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($row['deviasi'], 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @break

    {{-- ======================== BULANAN ======================== --}}
    @case('bulanan')
    <div class="overflow-x-auto">
        <table class="min-w-full border text-sm divide-y divide-gray-200">
            <thead class="bg-gray-50 text-gray-700 uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">Bidang</th>
                    <th class="px-4 py-2 text-left">Nama Kegiatan</th>
                    <th class="px-4 py-2 text-left">Realisasi Anggaran</th>
                    <th class="px-4 py-2 text-left">Deviasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['bulanan'] ?? [] as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $item['bidang'] ?? '-' }}</td>
                    <td class="px-4 py-2 font-medium">{{ $item['nama'] ?? '-' }}</td>
                    <td class="px-4 py-2">Rp {{ number_format($item['anggaran'] ?? 0, 0, ',', '.') }}</td>
                    <td class="px-4 py-2 {{ ($item['deviasi'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($item['deviasi'] ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @break

    {{-- ======================== TRIWULAN ======================== --}}
    @case('triwulan')
    <div class="overflow-x-auto mt-3">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">
            {{ $data['label'] ?? 'Rekap Triwulan' }}
        </h3>

        {{-- RINGKASAN --}}
        @if(isset($data['ringkasan']))
        <div class="mb-3 text-sm text-gray-700">
            <p>Total Kegiatan: <b>{{ $data['ringkasan']['total'] }}</b></p>
            <p>Rata-rata Fisik: <b>{{ $data['ringkasan']['rata_fisik'] }}%</b></p>
            <p>Realisasi Anggaran: <b>Rp {{ number_format($data['ringkasan']['anggaran'], 0, ',', '.') }}</b></p>
            <p>Persentase: <b>{{ $data['ringkasan']['persentase'] }}%</b></p>
        </div>
        @endif

        {{-- TABEL DETAIL --}}
        <table class="min-w-full border text-sm divide-y divide-gray-200">
            <thead class="bg-gray-50 text-gray-700 uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">Bidang</th>
                    <th class="px-4 py-2 text-left">Nama Kegiatan</th>
                    <th class="px-4 py-2 text-left">Jumlah Anggaran</th>
                    <th class="px-4 py-2 text-left">Realisasi Triwulan</th>
                    <th class="px-4 py-2 text-left">Sisa Anggaran</th>
                    <th class="px-4 py-2 text-left">Deviasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['kegiatans'] ?? [] as $k)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $k['bidang'] }}</td>
                    <td class="px-4 py-2">{{ $k['nama'] }}</td>
                    <td class="px-4 py-2">Rp {{ number_format($k['anggaran'], 0, ',', '.') }}</td>
                    <td class="px-4 py-2">Rp {{ number_format($k['realisasi'], 0, ',', '.') }}</td>
                    <td class="px-4 py-2">Rp {{ number_format($k['sisa'], 0, ',', '.') }}</td>
                    <td class="px-4 py-2 {{ $k['deviasi'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($k['deviasi'], 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500">
                        Tidak ada data kegiatan pada triwulan ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @break



    {{-- ======================== TAHUNAN ======================== --}}
    @case('tahunan')
    <div class="overflow-x-auto">
        <table class="min-w-full border text-sm divide-y divide-gray-200">
            <thead class="bg-gray-50 text-gray-700 uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">Bidang</th>
                    <th class="px-4 py-2 text-left">Nama Kegiatan</th>
                    <th class="px-4 py-2 text-left">Realisasi Anggaran</th>
                    <th class="px-4 py-2 text-left">Deviasi</th>
                </tr>
            </thead>
            <tbody>
                @php
                $kegiatans = \App\Models\Kegiatan::with('bidang')
                ->whereYear('created_at', $periode->year)
                ->get(['nama','bidang_id','target_anggaran','current_budget_realization']);
                @endphp
                @foreach($kegiatans as $k)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $k->bidang->nama ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $k->nama }}</td>
                    <td class="px-4 py-2">Rp {{ number_format($k->current_budget_realization, 0, ',', '.') }}</td>
                    <td
                        class="px-4 py-2 {{ ($k->current_budget_realization - $k->target_anggaran) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($k->current_budget_realization - $k->target_anggaran, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @break

    @endswitch

    {{-- ===== FOOTER ===== --}}
    <div class="text-right text-xs text-gray-500 pt-4 border-t">
        <p>Laporan dibuat: {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
        <p>Oleh: Admin Sistem e-Kinerja DINPORAPAR</p>
    </div>
</div>