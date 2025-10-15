<div class="space-y-6">
    @php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;

    // Pastikan variabel tersedia
    $user = $user ?? Auth::user();
    $jenis_laporan = $jenis_laporan ?? 'kinerja_bidang';
    $periode = $periode ?? now();

    // Format periode
    $periodeFormat = (strlen($periode) === 7)
    ? Carbon::parse($periode)->translatedFormat('F Y')
    : Carbon::parse($periode . '-01')->translatedFormat('Y');

    // Format triwulan
    if ($jenis_laporan === 'triwulan') {
    $quarter = $data['quarter'] ?? ceil(Carbon::parse($periode)->month / 3);
    $map = [
    1 => 'Triwulan I (Januari – Maret)',
    2 => 'Triwulan II (April – Juni)',
    3 => 'Triwulan III (Juli – September)',
    4 => 'Triwulan IV (Oktober – Desember)',
    ];
    $periodeFormat = $map[$quarter] . ' ' . Carbon::parse($periode)->year;
    }

    // Role label dan bidang target
    if ($user->hasRole('admin')) {
    $roleLabel = 'Administrator DINPORAPAR';
    $laporanUntuk = 'Seluruh Bidang';
    } elseif ($user->hasRole('pimpinan')) {
    $roleLabel = 'Pimpinan Bidang ' . ($user->bidang->nama ?? '');
    $laporanUntuk = $user->bidang->nama ?? 'Bidang Terkait';
    } else {
    $roleLabel = $user->name;
    $laporanUntuk = $user->bidang->nama ?? 'Bidang Terkait';
    }
    @endphp

    {{-- === HEADER === --}}
    <div class="text-center border-b pb-4">
        <h2 class="text-2xl font-bold text-gray-900 uppercase tracking-wide">
            LAPORAN KINERJA {{ strtoupper($laporanUntuk) }}
        </h2>
        <p class="text-gray-700 text-lg font-semibold">
            Periode: {{ $periodeFormat }}
        </p>
        <p class="text-gray-500 text-sm italic mt-1">
            Staf Admin: {{ $roleLabel }}
        </p>
    </div>

    {{-- === ISI LAPORAN === --}}
    @if(isset($data) && count($data))
    @switch($jenis_laporan)
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
                @forelse($data['perBidang'] ?? [] as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 font-medium">{{ $row['nama'] }}</td>
                    <td class="px-4 py-2">
                        @if(!empty($row['kegiatans']))
                        <ul class="list-disc ml-4 text-gray-700 text-xs">
                            @foreach($row['kegiatans'] as $nama)
                            <li>{{ $nama }}</li>
                            @endforeach
                        </ul>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-2">
                        Rp {{ number_format($row['realisasi_anggaran'] ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2 {{ ($row['deviasi'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($row['deviasi'] ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-4 text-gray-500">
                        Tidak ada data kinerja bidang.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @break

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
                @forelse($data['bulanan'] ?? [] as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $item['bidang'] ?? '-' }}</td>
                    <td class="px-4 py-2 font-medium">{{ $item['nama'] ?? '-' }}</td>
                    <td class="px-4 py-2">
                        Rp {{ number_format($item['anggaran'] ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2 {{ ($item['deviasi'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($item['deviasi'] ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-4 text-gray-500">
                        Tidak ada data bulanan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @break

    @case('triwulan')
    <div class="overflow-x-auto mt-3">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">
            {{ $data['label'] ?? 'Rekap Triwulan' }}
        </h3>

        @if(isset($data['ringkasan']))
        <div class="mb-3 text-sm text-gray-700">
            <p>Total Kegiatan: <b>{{ $data['ringkasan']['total'] }}</b></p>
            <p>Rata-rata Fisik: <b>{{ $data['ringkasan']['rata_fisik'] }}%</b></p>
            <p>Realisasi Anggaran:
                <b>Rp {{ number_format($data['ringkasan']['anggaran'], 0, ',', '.') }}</b>
            </p>
            <p>Persentase: <b>{{ $data['ringkasan']['persentase'] }}%</b></p>
        </div>
        @endif

        <table class="min-w-full border text-sm divide-y divide-gray-200">
            <thead class="bg-gray-50 text-gray-700 uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">Bidang</th>
                    <th class="px-4 py-2 text-left">Nama Kegiatan</th>
                    <th class="px-4 py-2 text-left">Jumlah Anggaran</th>
                    <th class="px-4 py-2 text-left">Realisasi Triwulan (Rp)</th>
                    <th class="px-4 py-2 text-left">Sisa Anggaran</th>
                    <th class="px-4 py-2 text-left">Deviasi (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['kegiatans'] ?? [] as $k)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $k['bidang'] }}</td>
                    <td class="px-4 py-2">{{ $k['nama'] }}</td>
                    <td class="px-4 py-2">
                        Rp {{ number_format($k['anggaran'] ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2">
                        Rp {{ number_format($k['realisasi'] ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2">
                        Rp {{ number_format($k['sisa'] ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2 {{ ($k['deviasi'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($k['deviasi'] ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500">
                        Tidak ada data triwulan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @break
    @endswitch
    @else
    <div class="text-center text-gray-500 py-10">
        <i class="fas fa-folder-open text-5xl mb-3 opacity-40"></i>
        <p>Tidak ada data yang ditemukan.</p>
    </div>
    @endif

    {{-- === FOOTER === --}}
    <div class="text-right text-xs text-gray-500 pt-4 border-t">
        <p>Laporan dibuat: {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
        <p>Oleh: <b>{{ $roleLabel }}</b></p>
    </div>
</div>