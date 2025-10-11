<x-app-layout>
    <div class="space-y-6">
        {{-- ===== HEADER ===== --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-file-alt mr-3 text-blue-600"></i>
                Laporan Kinerja
            </h1>
            <p class="text-gray-600 mt-1">Generate dan export laporan kinerja berdasarkan periode dan jenis laporan</p>
        </div>

        {{-- ===== FORM FILTER ===== --}}
        <div class="bg-white rounded-lg shadow-sm p-6"
            x-data="{ jenis: 'kinerja_bidang', periode: '{{ date('Y-m') }}' }">
            <form id="laporanForm" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    {{-- Bidang --}}
                    <div>
                        <label class="text-sm font-medium text-gray-600 mb-1 block">Bidang</label>
                        <select name="bidang_id" class="form-select w-full">
                            <option value="">Semua Bidang</option>
                            @foreach($bidangs as $bidang)
                            <option value="{{ $bidang->id }}">{{ $bidang->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Jenis Laporan --}}
                    <div>
                        <label class="text-sm font-medium text-gray-600 mb-1 block">Jenis Laporan</label>
                        <select name="jenis_laporan" x-model="jenis" class="form-select w-full">
                            <option value="kinerja_bidang">Kinerja Bidang</option>
                            <option value="bulanan">Rekap Bulanan</option>
                            <option value="triwulan">Rekap Triwulan</option>
                            {{-- <option value="tahunan">Rekap Tahunan</option> --}}
                        </select>
                    </div>

                    {{-- Periode --}}
                    <div>
                        <label class="text-sm font-medium text-gray-600 mb-1 block">Periode</label>

                        {{-- Bulanan / Kinerja Bidang --}}
                        <template x-if="jenis === 'bulanan' || jenis === 'kinerja_bidang'">
                            <input type="month" name="periode" x-model="periode" class="form-input w-full">
                        </template>

                        {{-- Triwulan --}}
                        <template x-if="jenis === 'triwulan'">
                            <div class="flex flex-col space-y-2">
                                <select name="triwulan" class="form-select w-full">
                                    <option value="1">Triwulan I (Jan–Mar)</option>
                                    <option value="2">Triwulan II (Apr–Jun)</option>
                                    <option value="3">Triwulan III (Jul–Sep)</option>
                                    <option value="4">Triwulan IV (Okt–Des)</option>
                                </select>
                                <input type="month" name="periode" x-model="periode" class="hidden">
                            </div>
                        </template>

                        {{-- Tahunan --}}
                        <template x-if="jenis === 'tahunan'">
                            <input type="number" name="periode" min="2020" max="2030" value="{{ date('Y') }}"
                                class="form-input w-full">
                        </template>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex flex-wrap justify-between items-center pt-4 border-t gap-3">
                    <button type="button" onclick="generateLaporan()"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition">
                        <i class="fas fa-chart-line mr-2"></i> Generate
                    </button>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="exportExcel()"
                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-sm shadow-sm">
                            <i class="fas fa-file-excel mr-1"></i> Excel
                        </button>
                        <button type="button" onclick="exportPdf()"
                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-sm shadow-sm">
                            <i class="fas fa-file-pdf mr-1"></i> PDF
                        </button>
                        <button type="button" onclick="printLaporan()"
                            class="bg-cyan-600 hover:bg-cyan-700 text-white px-3 py-1.5 rounded-lg text-sm shadow-sm">
                            <i class="fas fa-print mr-1"></i> Print
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ===== PREVIEW LAPORAN ===== --}}
        <div id="reportPreview" class="bg-white rounded-lg shadow-sm p-6 mt-4">
            <div id="defaultMessage" class="text-center py-12 text-gray-500">
                <i class="fas fa-chart-pie text-6xl mb-3 opacity-30"></i>
                <h3 class="text-xl font-semibold mb-1">Laporan Siap Ditampilkan</h3>
                <p>Pilih parameter dan klik <b>Generate</b> untuk melihat hasil.</p>
            </div>

            <div id="reportContent" class="hidden"></div>
        </div>
    </div>

    {{-- ===== SCRIPT ===== --}}
    <script>
        async function generateLaporan() {
            const form = document.getElementById('laporanForm');
            const formData = new FormData(form);
            const res = await fetch('{{ route('laporan.generate') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            });

            const result = await res.json();
            const content = document.getElementById('reportContent');
            const defaultMsg = document.getElementById('defaultMessage');

            defaultMsg.classList.add('hidden');
            content.classList.remove('hidden');
            content.innerHTML = result.html;
        }

        function exportExcel() {
            const form = document.getElementById('laporanForm');
            form.action = '{{ route('laporan.export.excel') }}';
            form.method = 'POST';
            form.submit();
        }

        function exportPdf() {
            const form = document.getElementById('laporanForm');
            form.action = '{{ route('laporan.export.pdf') }}';
            form.method = 'POST';
            form.submit();
        }

        function printLaporan() {
            const reportContent = document.getElementById('reportContent');
            if (!reportContent || !reportContent.innerHTML.trim()) {
                alert('Tidak ada laporan untuk dicetak!');
                return;
            }

            const printWindow = window.open('', '', 'width=1200,height=900');
            printWindow.document.open();
            printWindow.document.write(`
                <html>
                <head>
                    <title>Cetak Laporan</title>
                    <link href="{{ mix('resources/css/app.css') }}" rel="stylesheet">
                    <style>
                        @page { size: A4; margin: 12mm; }
                        html, body {
                            font-family: 'Inter', sans-serif;
                            background: white;
                            color: #111827;
                        }
                        table {
                            border-collapse: collapse;
                            width: 100%;
                            font-size: 13px;
                        }
                        th, td {
                            border: 1px solid #e5e7eb;
                            padding: 6px 10px;
                        }
                        thead { background: #f9fafb; }
                    </style>
                </head>
                <body>
                    <div style="text-align:center;margin-bottom:16px;">
                        <img src="{{ asset('images/dinporapar.png') }}" class="w-16 mx-auto mb-2">
                        <h2 style="font-weight:bold;">Dinas Kepemudaan dan Olahraga dan Pariwisata</h2>
                    </div>
                    ${reportContent.innerHTML}
                    <script>window.onload=function(){setTimeout(()=>window.print(),600)}<\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        }
    </script>
</x-app-layout>