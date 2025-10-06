<x-app-layout>
    <div class="space-y-6">
        {{-- ===== HEADER ===== --}}
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl shadow-sm border border-blue-100 p-6">
            <h1 class="text-2xl font-semibold text-gray-900 flex items-center gap-3">
                <i class="fas fa-file-chart-column text-blue-600"></i>
                Laporan Per Bidang
            </h1>
            <p class="text-gray-600 mt-1">Generate dan export laporan kinerja per bidang</p>
        </div>

        {{-- ===== FILTER & GENERATE ===== --}}
        <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
            <form id="laporanForm" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Bidang --}}
                    <div class="flex flex-col">
                        <label class="text-xs text-gray-500 mb-1">Pilih Bidang</label>
                        <select name="bidang_id" class="form-minimal">
                            <option value="">Semua Bidang</option>
                            @foreach($bidangs as $bidang)
                            <option value="{{ $bidang->id }}">{{ $bidang->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Jenis --}}
                    <div class="flex flex-col">
                        <label class="text-xs text-gray-500 mb-1">Jenis Laporan</label>
                        <select name="jenis_laporan" class="form-minimal">
                            <option value="kinerja_bidang">Kinerja Bidang</option>
                            <option value="bulanan">Rekap Bulanan</option>
                            <option value="triwulan">Rekap Triwulan</option>
                            <option value="tahunan">Rekap Tahunan</option>
                        </select>
                    </div>

                    {{-- Periode --}}
                    <div class="flex flex-col">
                        <label class="text-xs text-gray-500 mb-1">Periode</label>
                        <input type="month" name="periode" class="form-minimal" value="{{ date('Y-m') }}">
                    </div>
                </div>

                <div class="flex flex-wrap justify-between items-center pt-4 border-t gap-2">
                    <button type="button" onclick="generateLaporan()" class="btn-minimal flex items-center gap-2">
                        <i class="fas fa-chart-bar"></i> Generate
                    </button>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="exportExcel()"
                            class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg shadow-sm transition">
                            <i class="fas fa-file-excel mr-1"></i> Excel
                        </button>
                        <button type="button" onclick="exportPdf()"
                            class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg shadow-sm transition">
                            <i class="fas fa-file-pdf mr-1"></i> PDF
                        </button>
                        <button type="button" onclick="printLaporan()"
                            class="bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg shadow-sm transition">
                            <i class="fas fa-print mr-1"></i> Print
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ===== PREVIEW LAPORAN ===== --}}
        <div id="reportPreview" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
            <div id="defaultMessage" class="text-center py-16 text-gray-500">
                <i class="fas fa-chart-pie text-6xl mb-4 opacity-30"></i>
                <h3 class="text-xl font-semibold mb-2">Laporan Siap Ditampilkan</h3>
                <p>Pilih parameter dan klik <b>Generate</b> untuk melihat hasil</p>
            </div>

            <div id="reportContent" class="hidden"></div>
        </div>
    </div>

    {{-- ===== SCRIPT ===== --}}
    <script>
        async function generateLaporan() {
            const form = document.getElementById('laporanForm');
            const formData = new FormData(form);

            document.getElementById('defaultMessage').innerHTML = `
                <div class="text-center py-16">
                    <i class="fas fa-spinner fa-spin text-6xl text-blue-600 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Mengambil Data...</h3>
                </div>
            `;

            const res = await fetch('{{ route('laporan.generate') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            });

            const result = await res.json();
            document.getElementById('defaultMessage').classList.add('hidden');
            const content = document.getElementById('reportContent');
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
                alert('Tidak ada laporan yang bisa dicetak!');
                return;
            }

            // Ambil isi laporan, hapus overflow-x-auto agar tidak muncul scroll
            let content = reportContent.innerHTML.replace(/overflow-x-auto/g, 'overflow-visible');

            // Buka jendela print baru
            const printWindow = window.open('', '', 'width=1200,height=900');
            printWindow.document.open();

            printWindow.document.write(`
                <html>
                <head>
                    <title>Cetak Laporan</title>
                    <link href="{{ mix('resources/css/app.css') }}" rel="stylesheet">
                    <style>
                        /* === PENGATURAN HALAMAN === */
                        @page { 
                            size: A4; 
                            margin: 12mm; 
                        }

                        html, body {
                            width: 100%;
                            height: auto;
                            margin: 0;
                            padding: 0;
                            background: white;
                            color: #111827;
                            font-family: 'Inter', sans-serif;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                            overflow: visible !important;
                        }

                        * {
                            box-sizing: border-box;
                        }

                        /* === TABEL === */
                        table {
                            width: 100% !important;
                            border-collapse: collapse !important;
                            font-size: 13px;
                        }

                        th, td {
                            border: 1px solid #e5e7eb;
                            padding: 6px 10px;
                            text-align: left;
                        }

                        thead {
                            background: #f9fafb !important;
                        }

                        /* === HILANGKAN OVERFLOW SAAT PRINT === */
                        .overflow-x-auto {
                            overflow: visible !important;
                        }

                        /* === TAMPILAN HEADER === */
                        .report-header {
                            text-align: center;
                            margin-bottom: 20px;
                            border-bottom: 1px solid #d1d5db;
                            padding-bottom: 10px;
                        }

                        .report-header h2 {
                            font-size: 20px;
                            font-weight: 700;
                            margin-bottom: 2px;
                        }

                        .report-header p {
                            font-size: 13px;
                            color: #6b7280;
                            margin: 0;
                        }

                        /* === FOOTER === */
                        .report-footer {
                            text-align: right;
                            font-size: 11px;
                            color: #6b7280;
                            margin-top: 24px;
                            border-top: 1px solid #e5e7eb;
                            padding-top: 6px;
                        }

                        /* === RESPONSIVE FIX === */
                        @media print {
                            html, body {
                                overflow: visible !important;
                            }
                            .overflow-x-auto {
                                overflow: visible !important;
                            }
                            table {
                                width: 100% !important;
                                table-layout: auto !important;
                            }
                        }
                    </style>
                </head>
                <body class="p-8">

                    <div class="report-header">
                        <img src="{{ asset('images/dinporapar.png') }}" alt="Logo" class="w-16 mx-auto mb-2">
                        <p>Dinas Pemuda, Olahraga, dan Pariwisata Kabupaten Pekalongan</p>
                        
                    </div>

                    ${content}

                    <div class="report-footer">
                        <p>Dicetak pada: ${new Date().toLocaleDateString('id-ID')} ${new Date().toLocaleTimeString('id-ID')} WIB</p>
                        <p>Oleh: Admin Sistem e-Kinerja DINPORAPAR</p>
                    </div>

                    <script>
                        window.onload = function() {
                            setTimeout(() => window.print(), 800);
                        };
                    <\/script>

                </body>
                </html>
            `);

            printWindow.document.close();
        }
    </script>


</x-app-layout>