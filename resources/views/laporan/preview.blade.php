<div class="space-y-6">
    {{-- ===== HEADER ===== --}}
    @php
    use Carbon\Carbon;
    $periodeFormat = Carbon::parse($periode)->translatedFormat('F Y');
    $jumlahBidang = $data['per_bidang']->count();
    $namaBidang = $jumlahBidang === 1 ? $data['per_bidang']->first()['nama'] : null;
    @endphp

    <div class="text-center border-b pb-4">
        <h2 class="text-2xl font-bold text-gray-900 tracking-wide">
            LAPORAN KINERJA
            @if($namaBidang)
            {{ strtoupper($namaBidang) }}
            @else
            SEMUA BIDANG
            @endif
        </h2>
        <p class="text-gray-600">Periode: {{ $periodeFormat }}</p>
    </div>

    {{-- ===== STATISTIK RINGKAS ===== --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-gray-50 p-4 rounded-lg text-center">
            <p class="text-xs text-gray-500 uppercase">Total Kegiatan</p>
            <h4 class="text-3xl font-bold text-blue-600">{{ $data['summary']['total_kegiatan'] }}</h4>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg text-center">
            <p class="text-xs text-gray-500 uppercase">Selesai</p>
            <h4 class="text-3xl font-bold text-green-600">{{ $data['summary']['selesai'] }}</h4>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg text-center">
            <p class="text-xs text-gray-500 uppercase">Dalam Progress</p>
            <h4 class="text-3xl font-bold text-yellow-600">{{ $data['summary']['dalam_progress'] }}</h4>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg text-center">
            <p class="text-xs text-gray-500 uppercase">Terlambat</p>
            <h4 class="text-3xl font-bold text-red-600">{{ $data['summary']['terlambat'] }}</h4>
        </div>
    </div>

    {{-- ===== TABEL PER BIDANG ===== --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border text-sm">
            <thead class="bg-gray-50">
                <tr class="text-gray-600">
                    <th class="px-6 py-3 text-left font-medium uppercase">Bidang</th>
                    <th class="px-6 py-3 text-left font-medium uppercase">Total Kegiatan</th>
                    <th class="px-6 py-3 text-left font-medium uppercase">Rata-rata Capaian</th>
                    <th class="px-6 py-3 text-left font-medium uppercase">Total Anggaran</th>
                    <th class="px-6 py-3 text-left font-medium uppercase">Realisasi Anggaran</th>
                    <th class="px-6 py-3 text-left font-medium uppercase">Persentase</th>
                    <th class="px-6 py-3 text-left font-medium uppercase">Deviasi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($data['per_bidang'] as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium text-gray-800">{{ $row['nama'] }}</td>
                    <td class="px-6 py-3">{{ $row['total_kegiatan'] }}</td>
                    <td class="px-6 py-3">{{ number_format($row['avg_capaian'], 1) }}%</td>
                    <td class="px-6 py-3">Rp {{ number_format($row['total_anggaran'], 0, ',', '.') }}</td>
                    <td class="px-6 py-3">Rp {{ number_format($row['realisasi_anggaran'], 0, ',', '.') }}</td>
                    <td
                        class="px-6 py-3 font-medium
                        {{ $row['persentase_anggaran'] >= 90 ? 'text-green-600' : ($row['persentase_anggaran'] >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ number_format($row['persentase_anggaran'], 1) }}%
                    </td>
                    <td
                        class="px-6 py-3 font-medium
                        {{ $row['deviasi'] > 0 ? 'text-green-600' : ($row['deviasi'] < 0 ? 'text-red-600' : 'text-gray-600') }}">
                        Rp {{ number_format($row['deviasi'], 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ===== FOOTER ===== --}}
    <div class="text-right text-xs text-gray-500 pt-4 border-t">
        <p>Laporan dibuat: {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
        <p>Oleh: Admin Sistem e-Kinerja DINPORAPAR</p>
    </div>
</div>