<style>
    .table-fixed-cols {
        table-layout: fixed;
        width: 100%;
    }

    .w-col-nama {
        width: 350px;
        max-width: 350px;
    }

    .w-col-anggaran {
        width: 110px;
    }

    .w-col-realisasi {
        width: 110px;
    }

    .w-col-sisa {
        width: 90px;
    }

    .w-col-deviasi {
        width: 80px;
    }

    .w-col-targetfisik {
        width: 85px;
    }

    .w-col-realfisik {
        width: 85px;
    }
</style>

<div class="space-y-6">
    @php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;

    $user = $user ?? Auth::user();
    $jenis = $jenis_laporan ?? 'kinerja_bidang';
    $periode = $periode instanceof Carbon ? $periode : Carbon::parse($periode);

    // label periode
    $periodeRencana = null;
    if (!empty($data['kegiatans'])) {
    $firstKegiatan = collect($data['kegiatans'])->first();
    $periodeRencana = $firstKegiatan['periode'] ?? ($firstKegiatan['periode_type'] ?? null);
    }

    if ($periodeRencana) {
    if (str_starts_with(strtolower($periodeRencana), 'q')) {
    $periodeLabel = 'Triwulan ' . substr($periodeRencana, 1);
    } elseif (strtolower($periodeRencana) === 'tahunan') {
    $periodeLabel = 'Tahunan ' . ($periode->year ?? now()->year);
    } elseif (strtolower($periodeRencana) === 'bulanan') {
    $periodeLabel = 'Bulanan ' . ($periode->translatedFormat('F Y'));
    } else {
    $periodeLabel = ucfirst($periodeRencana) . ' ' . ($periode->year ?? now()->year);
    }
    } else {
    $periodeLabel = $data['label'] ?? ($periode->translatedFormat('F Y'));
    }

    if ($user->hasRole('admin')) {
    $roleLabel = 'Administrator DINPORAPAR';
    } elseif ($user->hasRole('kabid')) {
    $roleLabel = 'Kabid ' . ($user->bidang->nama ?? '');
    } else {
    $roleLabel = $user->name;
    }
    @endphp

    {{-- HEADER --}}
    <div class="text-center border-b pb-4">
        <h2 class="text-2xl font-bold text-gray-900 uppercase tracking-wide">
            LAPORAN KINERJA {{ strtoupper($laporanUntuk ?? 'DINAS') }}
        </h2>
        <p class="text-gray-700 text-lg font-semibold">
            Periode: {{ $periodeLabel ?? 'Tidak Ditentukan' }}
        </p>
        <p class="text-gray-500 text-sm italic mt-1">
            Disusun berdasarkan Rencana Kegiatan<br>Oleh: {{ $roleLabel }}
        </p>
    </div>

    {{-- RINGKASAN --}}
    @if(!empty($data['ringkasan']))
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="border rounded-lg p-4">
            <div class="text-xs text-gray-500">Total Kegiatan</div>
            <div class="text-xl font-semibold">{{ $data['ringkasan']['total_kegiatan'] ?? 0 }}</div>
        </div>
        <div class="border rounded-lg p-4">
            <div class="text-xs text-gray-500">Total Anggaran</div>
            <div class="text-xl font-semibold">
                Rp {{ number_format($data['ringkasan']['total_pagu'] ?? 0, 0, ',', '.') }}
            </div>
        </div>
        <div class="border rounded-lg p-4">
            <div class="text-xs text-gray-500">Total Realisasi</div>
            <div class="text-xl font-semibold">
                Rp {{ number_format($data['ringkasan']['total_realisasi'] ?? 0, 0, ',', '.') }}
            </div>
        </div>
        <div class="border rounded-lg p-4">
            <div class="text-xs text-gray-500">Persentase & Rata fisik</div>
            <div class="text-xl font-semibold">
                {{ number_format($data['ringkasan']['persentase'] ?? 0, 1, ',', '.') }}%
                · {{ number_format($data['ringkasan']['rata_fisik'] ?? 0, 1, ',', '.') }}%
            </div>
        </div>
    </div>
    @endif

    {{-- TABEL --}}
    @if(!empty($data['kegiatans']) && count($data['kegiatans']))
    <div class="overflow-x-auto">
        <table class="table-fixed-cols border text-sm divide-y divide-gray-200">
            <thead class="bg-gray-50 text-gray-700 uppercase">
                <tr>
                    <th class="px-4 py-2 text-left w-col-nama">Kegiatan (Bidang)</th>
                    <th class="px-4 py-2 text-right w-col-anggaran">Target Anggaran</th>
                    <th class="px-4 py-2 text-right w-col-realisasi">Realisasi</th>
                    <th class="px-4 py-2 text-right w-col-sisa">Sisa</th>
                    <th class="px-4 py-2 text-right w-col-deviasi">Deviasi (%)</th>
                    <th class="px-4 py-2 text-right w-col-targetfisik">Target Fisik (%)</th>
                    <th class="px-4 py-2 text-right w-col-realfisik">Realisasi Fisik (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['kegiatans'] as $k)
                @php $dvK = $k['deviasi_pct'] ?? null; @endphp
                <tr class="bg-white hover:bg-gray-50">
                    <td class="px-4 py-2 font-semibold text-gray-900">
                        {{ $k['nama'] }}
                        <div class="text-xs text-gray-500">Bidang: {{ $k['bidang'] }}</div>
                    </td>
                    <td class="px-4 py-2 text-right">Rp {{ number_format($k['pagu'] ?? 0, 0, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">Rp {{ number_format($k['realisasi'] ?? 0, 0, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">
                        Rp {{ number_format($k['sisa'] ?? max(($k['pagu'] ?? 0)-($k['realisasi'] ??0),0),0,',','.') }}
                    </td>
                    <td class="px-4 py-2 text-right {{ $dvK >=0 ? 'text-green-600':'text-red-600' }}">
                        {{ is_null($dvK)?'-':number_format($dvK,1,',','.').' %' }}
                    </td>
                    <td class="px-4 py-2 text-right">{{ number_format($k['target_fisik'] ?? 0,1,',','.') }}%</td>
                    <td class="px-4 py-2 text-right">{{ number_format($k['realisasi_fisik'] ?? 0,1,',','.') }}%</td>
                </tr>

                {{-- SUBKEGIATAN --}}
                @if(!empty($k['subkegiatans']))
                <tr>
                    <td colspan="7" class="p-0">
                        <table class="table-fixed-cols text-xs border-t">
                            <thead class="bg-green-100">
                                <tr>
                                    <th class="px-6 py-2 text-left w-col-nama">Subkegiatan</th>
                                    <th class="px-2 py-2 text-right w-col-anggaran">Target Anggaran</th>
                                    <th class="px-2 py-2 text-right w-col-realisasi">Realisasi</th>
                                    <th class="px-2 py-2 text-right w-col-sisa">Sisa</th>
                                    <th class="px-2 py-2 text-right w-col-deviasi">Deviasi (%)</th>
                                    <th class="px-2 py-2 text-right w-col-targetfisik">Target Fisik (%)</th>
                                    <th class="px-2 py-2 text-right w-col-realfisik">Realisasi Fisik (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($k['subkegiatans'] as $s)
                                @php $dvS = $s['deviasi_pct'] ?? null; @endphp
                                <tr class="bg-green-50">
                                    <td class="px-6 py-2 font-medium text-gray-800">{{ $s['nama'] }}</td>
                                    <td class="px-2 py-2 text-right">Rp {{ number_format($s['pagu'] ?? 0,0,',','.') }}
                                    </td>
                                    <td class="px-2 py-2 text-right">Rp {{ number_format($s['realisasi'] ?? 0,0,',','.')
                                        }}</td>
                                    <td class="px-2 py-2 text-right">
                                        Rp {{ number_format($s['sisa'] ??
                                        max(($s['pagu']??0)-($s['realisasi']??0),0),0,',','.') }}
                                    </td>
                                    <td class="px-2 py-2 text-right {{ $dvS>=0?'text-green-700':'text-red-700' }}">
                                        {{ is_null($dvS)?'-':number_format($dvS,1,',','.').' %' }}
                                    </td>
                                    <td class="px-2 py-2 text-right">{{ number_format($s['target_fisik']??0,1,',','.')
                                        }}%</td>
                                    <td class="px-2 py-2 text-right">{{
                                        number_format($s['realisasi_fisik']??0,1,',','.') }}%</td>
                                </tr>

                                {{-- RINCIAN --}}
                                @if(!empty($s['rincians']))
                                <tr>
                                    <td colspan="7" class="p-0">
                                        <table class="table-fixed-cols text-[11px] border-t">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-10 py-1.5 text-left w-col-nama">Rincian (Uraian)</th>
                                                    <th class="px-2 py-1.5 text-right w-col-anggaran">Target Anggaran
                                                    </th>
                                                    <th class="px-2 py-1.5 text-right w-col-realisasi">Realisasi</th>
                                                    <th class="px-2 py-1.5 text-right w-col-sisa">Sisa</th>
                                                    <th class="px-2 py-1.5 text-right w-col-deviasi">Deviasi (%)</th>
                                                    <th class="px-2 py-1.5 text-right w-col-targetfisik">Target Fisik
                                                        (%)</th>
                                                    <th class="px-2 py-1.5 text-right w-col-realfisik">Realisasi Fisik
                                                        (%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($s['rincians'] as $r)
                                                @php $devPct=$r['deviasi_pct']??null; @endphp
                                                <tr class="bg-white">
                                                    <td class="px-10 py-1.5 text-gray-700">{{ $r['uraian'] }}</td>
                                                    <td class="px-2 py-1.5 text-right">Rp {{
                                                        number_format($r['anggaran']??0,0,',','.') }}</td>
                                                    <td class="px-2 py-1.5 text-right">Rp {{
                                                        number_format($r['realisasi']??0,0,',','.') }}</td>
                                                    <td class="px-2 py-1.5 text-right">
                                                        Rp {{ number_format($r['sisa'] ??
                                                        max(($r['anggaran']??0)-($r['realisasi']??0),0),0,',','.') }}
                                                    </td>
                                                    <td
                                                        class="px-2 py-1.5 text-right {{ $devPct>=0?'text-green-600':'text-red-600' }}">
                                                        {{ is_null($devPct)?'-':number_format($devPct,1,',','.').' %' }}
                                                    </td>
                                                    <td class="px-2 py-1.5 text-right">{{
                                                        number_format($r['target_fisik']??100,1,',','.') }}%</td>
                                                    <td class="px-2 py-1.5 text-right">{{
                                                        is_null($r['fisik'])?'-':number_format($r['fisik'],1,',','.').'
                                                        %' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center text-gray-500 py-10">
        <i class="fas fa-folder-open text-5xl mb-3 opacity-40"></i>
        <p>Tidak ada data yang ditemukan untuk periode ini.</p>
    </div>
    @endif
</div>

{{-- FOOTER CETAK --}}
<div class="text-right text-xs text-gray-500 pt-4 border-t mt-8">
    <p>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
    <p>Oleh: <b>{{ $roleLabel }}</b></p>
</div>