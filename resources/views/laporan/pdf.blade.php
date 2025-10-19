{{-- resources/views/laporan/pdf.blade.php --}}
@php
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

$user = $user ?? Auth::user();
$periode = $periode instanceof Carbon ? $periode : Carbon::parse($periode);
$periodeLabel = $data['label'] ?? $periode->translatedFormat('F Y');

// Role label
if ($user->hasRole('admin')) {
$roleLabel = 'Administrator DINPORAPAR';
} elseif ($user->hasRole('kabid')) {
$roleLabel = 'Kabid ' . ($user->bidang->nama ?? '');
} else {
$roleLabel = $user->name;
}

// TTD
$namaTTD = $penandatangan->name ?? 'Kepala Dinas';
$nipTTD = $penandatangan->nip ?? null;

// Logo (gunakan path lokal agar DomPDF bisa render)
$logoPath = public_path('images/dinporapar.png');
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Kinerja</title>
    <style>
        @page {
            size: A4 landscape;
            /* <— landscape */
            margin: 12mm;
        }

        html,
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #111827;
            font-size: 12px;
        }

        h1,
        h2,
        h3 {
            margin: 0;
        }

        .center {
            text-align: center;
        }

        .mb-1 {
            margin-bottom: 4px;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .mb-3 {
            margin-bottom: 12px;
        }

        .mb-4 {
            margin-bottom: 16px;
        }

        .mt-2 {
            margin-top: 8px;
        }

        .mt-4 {
            margin-top: 16px;
        }

        .mt-8 {
            margin-top: 32px;
        }

        .pt-4 {
            padding-top: 16px;
        }

        .pb-4 {
            padding-bottom: 16px;
        }

        .border-b {
            border-bottom: 1px solid #e5e7eb;
        }

        .border {
            border: 1px solid #e5e7eb;
        }

        .rounded {
            border-radius: 6px;
        }

        .text-sm {
            font-size: 12px;
        }

        .text-xs {
            font-size: 11px;
        }

        .text-gray {
            color: #6b7280;
        }

        .text-green {
            color: #059669;
        }

        .text-red {
            color: #dc2626;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f9fafb;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
        }

        .sub-header {
            background: #d1fae5;
        }

        /* hijau muda subkegiatan */
        .sub-row {
            background: #ecfdf5;
        }

        /* hijau lebih muda isi subkegiatan */
        .fit {
            width: 1%;
            white-space: nowrap;
        }

        /* blok arahan & tindak lanjut */
        .box {
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }

        .box-head {
            background: #f3f4f6;
            padding: 6px 10px;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }

        .box-body {
            padding: 10px;
        }

        .dash {
            border-bottom: 1px dashed #d1d5db;
            height: 22px;
        }

        /* tanda tangan */
        .ttd-wrap {
            text-align: right;
            margin-top: 28px;
        }

        .ttd-block {
            display: inline-block;
            text-align: center;
        }

        .ttd-gap {
            height: 72px;
        }

        .underline {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="center mb-3">
        @if(file_exists($logoPath))
        <img src="{{ public_path('images/dinporapar.png') }}" style="height:56px;">
        @endif
        <h2 style="font-weight:bold;">LAPORAN KINERJA {{ strtoupper($laporanUntuk ?? 'DINAS') }}</h2>
        <div class="text-sm">Periode: {{ $periodeLabel }}</div>
        <div class="text-xs text-gray mt-1">Disusun oleh: {{ $roleLabel }}</div>
    </div>

    {{-- RINGKASAN --}}
    @if(!empty($data['ringkasan']))
    <table class="mb-3">
        <tr>
            <td class="border">
                <div class="text-xs text-gray">Total Kegiatan</div>
                <div style="font-weight:600">{{ $data['ringkasan']['total_kegiatan'] ?? 0 }}</div>
            </td>
            <td class="border">
                <div class="text-xs text-gray">Total Pagu</div>
                <div style="font-weight:600">Rp {{ number_format($data['ringkasan']['total_pagu'] ?? 0, 0, ',', '.') }}
                </div>
            </td>
            <td class="border">
                <div class="text-xs text-gray">Total Realisasi</div>
                <div style="font-weight:600">Rp {{ number_format($data['ringkasan']['total_realisasi'] ?? 0, 0, ',',
                    '.') }}</div>
            </td>
            <td class="border">
                <div class="text-xs text-gray">Persentase & Rata fisik</div>
                <div style="font-weight:600">{{ number_format($data['ringkasan']['persentase'] ?? 0, 1, ',', '.') }}% ·
                    {{ number_format($data['ringkasan']['rata_fisik'] ?? 0, 1, ',', '.') }}%</div>
            </td>
        </tr>
    </table>
    @endif

    {{-- TABEL KEGIATAN -> SUB -> RINCIAN --}}
    @if(!empty($data['kegiatans']) && count($data['kegiatans']))
    <table>
        <thead>
            <tr>
                <th class="text-left">Kegiatan (Bidang)</th>
                <th class="text-right fit">Pagu</th>
                <th class="text-right fit">Realisasi</th>
                <th class="text-right fit">Sisa</th>
                <th class="text-right fit">Progress</th>
                <th class="text-right fit">Deviasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['kegiatans'] as $k)
            {{-- Row Kegiatan --}}
            <tr>
                <td>
                    <div style="font-weight:600">{{ $k['nama'] }}</div>
                    <div class="text-xs text-gray">Bidang: {{ $k['bidang'] }}</div>
                </td>
                <td class="text-right">Rp {{ number_format($k['pagu'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($k['realisasi'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($k['sisa'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($k['progress'] ?? 0, 1, ',', '.') }}%</td>
                <td class="text-right {{ ($k['deviasi'] ?? 0) >= 0 ? 'text-green' : 'text-red' }}">
                    Rp {{ number_format($k['deviasi'] ?? 0, 0, ',', '.') }}
                </td>
            </tr>

            {{-- Subkegiatan --}}
            @if(!empty($k['subkegiatans']))
            <tr>
                <td colspan="6" style="padding:0;border-top:none;">
                    <table>
                        <thead>
                            <tr class="sub-header">
                                <th class="text-left">Subkegiatan</th>
                                <th class="text-right fit">Pagu</th>
                                <th class="text-right fit">Realisasi</th>
                                <th class="text-right fit">Sisa</th>
                                <th class="text-right fit">Progress</th>
                                <th class="text-right fit">Deviasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($k['subkegiatans'] as $s)
                            <tr class="sub-row">
                                <td style="font-weight:600">{{ $s['nama'] }}</td>
                                <td class="text-right">Rp {{ number_format($s['pagu'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($s['realisasi'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($s['sisa'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($s['progress'] ?? 0, 1, ',', '.') }}%</td>
                                @php
                                // deviasi sub (Rp) = realisasi - pagu
                                $devS = ($s['realisasi'] ?? 0) - ($s['pagu'] ?? 0);
                                @endphp
                                <td class="text-right {{ $devS >= 0 ? 'text-green' : 'text-red' }}">
                                    Rp {{ number_format($devS, 0, ',', '.') }}
                                </td>
                            </tr>

                            {{-- Rincian --}}
                            @if(!empty($s['rincians']))
                            <tr>
                                <td colspan="6" style="padding:0;border-top:none;">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th class="text-left">Rincian (Uraian)</th>
                                                <th class="text-right fit">Anggaran</th>
                                                <th class="text-right fit">Realisasi</th>
                                                <th class="text-right fit">Sisa</th>
                                                <th class="text-right fit">Deviasi (%)</th>
                                                <th class="text-right fit">Fisik (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($s['rincians'] as $r)
                                            <tr>
                                                <td class="text-left" style="padding-left:16px">{{ $r['uraian'] }}</td>
                                                <td class="text-right">Rp {{ number_format($r['anggaran'] ?? 0, 0, ',',
                                                    '.') }}</td>
                                                <td class="text-right">Rp {{ number_format($r['realisasi'] ?? 0, 0, ',',
                                                    '.') }}</td>
                                                <td class="text-right">Rp {{ number_format($r['sisa'] ?? 0, 0, ',', '.')
                                                    }}</td>
                                                @php $dp = $r['deviasi_pct'] ?? null; @endphp
                                                <td
                                                    class="text-right {{ is_null($dp) ? '' : ($dp >= 0 ? 'text-green' : 'text-red') }}">
                                                    {{ is_null($dp) ? '-' : number_format($dp, 1, ',', '.') . ' %' }}
                                                </td>
                                                <td class="text-right">
                                                    {{ is_null($r['fisik']) ? '-' : number_format($r['fisik'], 1, ',',
                                                    '.') . ' %' }}
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
        <tfoot>
            <tr>
                <td class="text-right" style="font-weight:600;">TOTAL</td>
                <td class="text-right" style="font-weight:600;">
                    Rp {{ number_format($data['ringkasan']['total_pagu'] ?? 0, 0, ',', '.') }}
                </td>
                <td class="text-right" style="font-weight:600;">
                    Rp {{ number_format($data['ringkasan']['total_realisasi'] ?? 0, 0, ',', '.') }}
                </td>
                @php
                $tp = (float)($data['ringkasan']['total_pagu'] ?? 0);
                $tr = (float)($data['ringkasan']['total_realisasi'] ?? 0);
                $ts = max($tp - $tr, 0);
                @endphp
                <td class="text-right" style="font-weight:600;">
                    Rp {{ number_format($ts, 0, ',', '.') }}
                </td>
                <td class="text-right" style="font-weight:600;">
                    {{ number_format($data['ringkasan']['persentase'] ?? 0, 1, ',', '.') }}%
                </td>
                <td class="text-right">—</td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="center text-gray" style="padding:32px 0;">
        <div style="font-size: 18px;">Tidak ada data yang ditemukan untuk periode ini.</div>
    </div>
    @endif

    {{-- ARAHAN (atas) --}}
    <div class="mt-4">
        <div class="box">
            <div class="box-head">Arahan</div>
            <div class="box-body">
                <div class="dash"></div>
                <div class="dash"></div>
                <div class="dash"></div>
            </div>
        </div>
    </div>

    {{-- TINDAK LANJUT (bawah) --}}
    <div class="mt-4">
        <div class="box">
            <div class="box-head">Tindak Lanjut</div>
            <div class="box-body">
                <div class="dash"></div>
                <div class="dash"></div>
                <div class="dash"></div>
            </div>
        </div>
    </div>

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

</body>

</html>