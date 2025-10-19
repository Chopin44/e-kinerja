<div class="space-y-6">
    @php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;

    $user = $user ?? Auth::user();
    $jenis = $jenis_laporan ?? 'kinerja_bidang';
    $periode = $periode instanceof Carbon ? $periode : Carbon::parse($periode);
    $periodeLabel = $data['label'] ?? ($periode->translatedFormat('F Y'));

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
            Periode: {{ $periodeLabel }}
        </p>
        <p class="text-gray-500 text-sm italic mt-1">
            Disusun oleh: {{ $roleLabel }}
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

    {{-- TABEL HIRARKI --}}
    @if(!empty($data['kegiatans']) && count($data['kegiatans']))
    <div class="overflow-x-auto">
        <table class="min-w-full border text-sm divide-y divide-gray-200">
            <thead class="bg-gray-50 text-gray-700 uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">Kegiatan (Bidang)</th>
                    <th class="px-4 py-2 text-right">Target Anggaran</th>
                    <th class="px-4 py-2 text-right">Realisasi</th>
                    <th class="px-4 py-2 text-right">Sisa</th>
                    <th class="px-4 py-2 text-right">Deviasi (%)</th>
                    <th class="px-4 py-2 text-right">Progress</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['kegiatans'] as $k)
                {{-- Kegiatan --}}
                @php $dvK = $k['deviasi_pct'] ?? null; @endphp
                <tr class="bg-white hover:bg-gray-50">
                    <td class="px-4 py-2 font-semibold text-gray-900">
                        {{ $k['nama'] }}
                        <div class="text-xs text-gray-500">Bidang: {{ $k['bidang'] }}</div>
                    </td>
                    <td class="px-4 py-2 text-right">
                        Rp {{ number_format($k['pagu'] ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2 text-right">
                        Rp {{ number_format($k['realisasi'] ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2 text-right">
                        Rp {{ number_format($k['sisa'] ?? max(($k['pagu'] ?? 0) - ($k['realisasi'] ?? 0), 0), 0, ',',
                        '.') }}
                    </td>
                    <td
                        class="px-4 py-2 text-right {{ is_null($dvK) ? '' : ($dvK >= 0 ? 'text-green-600' : 'text-red-600') }}">
                        {{ is_null($dvK) ? '-' : number_format($dvK, 1, ',', '.') . ' %' }}
                    </td>
                    <td class="px-4 py-2 text-right">
                        {{ number_format($k['progress'] ?? 0, 1, ',', '.') }}%
                    </td>
                </tr>

                {{-- Subkegiatan --}}
                @if(!empty($k['subkegiatans']))
                <tr>
                    <td colspan="6" class="p-0">
                        <table class="min-w-full text-xs border-t">
                            <thead class="bg-gray-50">
                                <tr class="bg-green-200">
                                    <th class="px-6 py-2 text-left">Subkegiatan</th>
                                    <th class="px-2 py-2 text-right">Target Anggaran</th>
                                    <th class="px-2 py-2 text-right">Realisasi</th>
                                    <th class="px-2 py-2 text-right">Sisa</th>
                                    <th class="px-2 py-2 text-right">Deviasi (%)</th>
                                    <th class="px-2 py-2 text-right">Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($k['subkegiatans'] as $s)
                                @php $dvS = $s['deviasi_pct'] ?? null; @endphp
                                <tr class="bg-green-200">
                                    <td class="px-6 py-2 font-medium text-gray-800">
                                        {{ $s['nama'] }}
                                    </td>
                                    <td class="px-2 py-2 text-right">
                                        Rp {{ number_format($s['pagu'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-2 py-2 text-right">
                                        Rp {{ number_format($s['realisasi'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-2 py-2 text-right">
                                        Rp {{ number_format($s['sisa'] ?? max(($s['pagu'] ?? 0) - ($s['realisasi'] ??
                                        0), 0), 0, ',', '.') }}
                                    </td>
                                    <td
                                        class="px-2 py-2 text-right {{ is_null($dvS) ? '' : ($dvS >= 0 ? 'text-green-700' : 'text-red-700') }}">
                                        {{ is_null($dvS) ? '-' : number_format($dvS, 1, ',', '.') . ' %' }}
                                    </td>
                                    <td class="px-2 py-2 text-right">
                                        {{ number_format($s['progress'] ?? 0, 1, ',', '.') }}%
                                    </td>
                                </tr>

                                {{-- Rincian --}}
                                @if(!empty($s['rincians']))
                                <tr>
                                    <td colspan="6" class="p-0">
                                        <table class="min-w-full text-[11px] border-t">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-10 py-1.5 text-left">Rincian (Uraian)</th>
                                                    <th class="px-2 py-1.5 text-right">Target Anggaran</th>
                                                    <th class="px-2 py-1.5 text-right">Realisasi</th>
                                                    <th class="px-2 py-1.5 text-right">Sisa</th>
                                                    <th class="px-2 py-1.5 text-right">Deviasi (%)</th>
                                                    <th class="px-2 py-1.5 text-right">Fisik (%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($s['rincians'] as $r)
                                                @php $devPct = $r['deviasi_pct'] ?? null; @endphp
                                                <tr class="bg-white">
                                                    <td class="px-10 py-1.5 text-gray-700">
                                                        {{ $r['uraian'] }}
                                                    </td>
                                                    <td class="px-2 py-1.5 text-right">
                                                        Rp {{ number_format($r['anggaran'] ?? 0, 0, ',', '.') }}
                                                    </td>
                                                    <td class="px-2 py-1.5 text-right">
                                                        Rp {{ number_format($r['realisasi'] ?? 0, 0, ',', '.') }}
                                                    </td>
                                                    <td class="px-2 py-1.5 text-right">
                                                        Rp {{ number_format($r['sisa'] ?? max(($r['anggaran'] ?? 0) -
                                                        ($r['realisasi'] ?? 0), 0), 0, ',', '.') }}
                                                    </td>
                                                    <td
                                                        class="px-2 py-1.5 text-right {{ is_null($devPct) ? '' : ($devPct >= 0 ? 'text-green-600' : 'text-red-600') }}">
                                                        {{ is_null($devPct) ? '-' : number_format($devPct, 1, ',', '.')
                                                        . ' %' }}
                                                    </td>
                                                    <td class="px-2 py-1.5 text-right">
                                                        {{ is_null($r['fisik']) ? '-' : number_format($r['fisik'], 1,
                                                        ',', '.') . ' %' }}
                                                    </td>
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

            {{-- FOOTER TOTAL --}}
            <tfoot class="bg-gray-50">
                <tr class="font-semibold">
                    <td class="px-4 py-2 text-right">TOTAL</td>
                    <td class="px-4 py-2 text-right">
                        Rp {{ number_format($data['ringkasan']['total_pagu'] ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2 text-right">
                        Rp {{ number_format($data['ringkasan']['total_realisasi'] ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2 text-right">
                        @php
                        $tp = (float)($data['ringkasan']['total_pagu'] ?? 0);
                        $tr = (float)($data['ringkasan']['total_realisasi'] ?? 0);
                        $ts = max($tp - $tr, 0);
                        @endphp
                        Rp {{ number_format($ts, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2 text-right">
                        {{ number_format($data['ringkasan']['persentase'] ?? 0, 1, ',', '.') }}%
                    </td>
                    <td class="px-4 py-2 text-right">—</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="text-center text-gray-500 py-10">
        <i class="fas fa-folder-open text-5xl mb-3 opacity-40"></i>
        <p>Tidak ada data yang ditemukan untuk periode ini.</p>
    </div>
    @endif

    {{-- ARAHAN & TINDAK LANJUT (VERTIKAL) --}}
    <div class="mt-8 space-y-6">
        {{-- Arahan --}}
        <div class="rounded-lg border border-gray-300 bg-white text-left">
            <div class="px-4 py-2 border-b bg-gray-50 font-semibold text-gray-700">
                Arahan
            </div>
            <div class="p-4">
                @for($i=0; $i<3; $i++) <div class="h-6 border-b border-dashed border-gray-300">
            </div>
            @endfor
        </div>
    </div>

    {{-- Tindak Lanjut --}}
    <div class="rounded-lg border border-gray-300 bg-white text-left">
        <div class="px-4 py-2 border-b bg-gray-50 font-semibold text-gray-700">
            Tindak Lanjut
        </div>
        <div class="p-4">
            @for($i=0; $i<3; $i++) <div class="h-6 border-b border-dashed border-gray-300">
        </div>
        @endfor
    </div>
</div>
</div>

{{-- TANDA TANGAN --}}
@php
$namaTTD = $penandatangan->name ?? 'Kepala Dinas';
$nipTTD = $penandatangan->nip ?? null;
@endphp

<div class="mt-10">
    <div class="text-right">
        <div class="inline-block text-center">
            <div class="font-normal text-sm text-black mb-2">
                Kajen,<span class="ml-12">{{ now()->translatedFormat('F Y') }}</span>
            </div>
            <div class="font-bold text-sm text-black">Kepala Dinas</div>
            <div class="font-bold text-sm text-black">Kepemudaan dan Olahraga dan Pariwisata</div>
            <div class="font-bold text-sm text-black">Kabupaten Pekalongan</div>

            <div class="h-20"></div>

            <div class="font-bold text-sm text-black underline">
                {{ $namaTTD }}
            </div>
            @if(!empty($nipTTD))
            <div class="font-bold text-sm text-black">NIP. {{ $nipTTD }}</div>
            @endif
        </div>
    </div>
</div>

{{-- FOOTER CETAK --}}
<div class="text-right text-xs text-gray-500 pt-4 border-t">
    <p>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
    <p>Oleh: <b>{{ $roleLabel }}</b></p>
</div>
</div>