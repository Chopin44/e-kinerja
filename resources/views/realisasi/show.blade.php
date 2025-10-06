<x-app-layout>
    <div class="space-y-8">

        {{-- ===== HEADER ===== --}}
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl shadow-sm border border-blue-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 flex items-center gap-3">
                        <i class="fas fa-clipboard-list text-blue-600"></i>
                        Detail Monitoring Kegiatan
                    </h1>
                    <p class="text-gray-600 mt-1 font-medium tracking-wide">{{ $kegiatan->nama }}</p>
                </div>
                <a href="{{ route('monitoring.index') }}"
                    class="px-4 py-2 rounded-lg bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 text-sm font-medium shadow-sm transition-all">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>

        {{-- ===== RINGKASAN KEGIATAN ===== --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Bidang</p>
                        <p class="font-medium text-gray-900">{{ $kegiatan->bidang->nama ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Penanggung Jawab</p>
                        <p class="font-medium text-gray-900">{{ $kegiatan->user->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Periode</p>
                        <p class="font-medium text-gray-900">{{ $kegiatan->periode ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Target Fisik</p>
                        <p class="font-medium text-gray-900">{{ $kegiatan->target_fisik }}%</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Target Anggaran</p>
                        <p class="font-medium text-gray-900">
                            Rp {{ number_format((float)$kegiatan->target_anggaran,0,',','.') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Status Evaluasi</p>
                        <p class="font-medium capitalize text-gray-900">
                            {{ str_replace('_',' ', $status_evaluasi ?? '-') }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    {{-- Progress Fisik --}}
                    <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-700">Progress Fisik Saat Ini</h3>
                            <span class="text-sm font-bold text-gray-900">{{ $current_progress }}%</span>
                        </div>
                        <div class="progress-bar mt-2">
                            <div class="progress-fill" style="width: {{ (float)$current_progress }}%"></div>
                        </div>
                    </div>

                    {{-- Realisasi Anggaran --}}
                    <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-700">Realisasi Anggaran</h3>
                            <span class="text-sm font-bold text-gray-900">{{ $budget_percentage }}%</span>
                        </div>
                        <div class="progress-bar mt-2">
                            <div class="progress-fill" style="width: {{ (float)$budget_percentage }}%"></div>
                        </div>
                        <div class="mt-2 text-sm">
                            <div class="text-gray-700 font-medium">
                                Rp {{ number_format((float)$budget_realization, 0, ',', '.') }}
                            </div>
                            <div class="text-gray-500 text-xs">
                                dari target Rp {{ number_format((float)$kegiatan->target_anggaran, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Evaluasi Terakhir --}}
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <i class="fas fa-comments text-blue-500"></i> Evaluasi Terakhir
                </h3>
                @if($latestEvaluation)
                <div class="space-y-2 text-sm">
                    <div><span class="text-gray-500">Tanggal:</span>
                        {{ optional($latestEvaluation->tanggal_evaluasi)->format('d/m/Y') }}</div>
                    <div><span class="text-gray-500">Status:</span>
                        {{ str_replace('_',' ', $latestEvaluation->status_evaluasi) }}</div>
                    <div><span class="text-gray-500">Evaluator:</span>
                        {{ $latestEvaluation->evaluator->name ?? '-' }}</div>
                    <div class="text-gray-500">Catatan:</div>
                    <div class="p-3 bg-gray-50 rounded">{{ $latestEvaluation->catatan_evaluasi }}</div>
                    @if(!empty($latestEvaluation->rekomendasi))
                    <div class="text-gray-500">Rekomendasi:</div>
                    <div class="p-3 bg-gray-50 rounded">{{ $latestEvaluation->rekomendasi }}</div>
                    @endif
                </div>
                @else
                <div class="text-sm text-gray-500">Belum ada evaluasi.</div>
                @endif
            </div>
        </div>

        {{-- ===== TIMELINE PROGRESS ===== --}}
        <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <i class="fas fa-stream text-blue-500"></i> Timeline Progress
            </h3>
            @if(count($timeline))
            <div class="overflow-x-auto relative">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 sticky top-0 shadow-sm">
                        <tr class="text-gray-600 uppercase text-xs tracking-wider">
                            <th class="px-6 py-3 text-left">Tanggal</th>
                            <th class="px-6 py-3 text-left">Fisik (%)</th>
                            <th class="px-6 py-3 text-left">Anggaran (Rp)</th>
                            <th class="px-6 py-3 text-left">Lokasi</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($timeline as $row)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-3">{{ $row['tanggal'] }}</td>
                            <td class="px-6 py-3">{{ $row['progress_fisik'] }}</td>
                            <td class="px-6 py-3">
                                Rp {{ number_format((float)$row['progress_anggaran'],0,',','.') }}
                            </td>
                            <td class="px-6 py-3">{{ $row['lokasi'] ?? '-' }}</td>
                            <td class="px-6 py-3">{{ ucfirst($row['status']) }}</td>
                            <td class="px-6 py-3 text-gray-700">{{ $row['catatan'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-sm text-gray-500">Belum ada realisasi tercatat.</div>
            @endif
        </div>

        {{-- ===== FORM EVALUASI UNTUK ADMIN (minimalis pendek) ===== --}}
        @auth
        @if(auth()->user()->role === 'admin')
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fas fa-edit text-blue-500"></i> Input Arahan
            </h3>

            {{-- Flash message --}}
            @if(session('success'))
            <div class="mb-3 p-3 rounded bg-green-50 text-green-700 text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="mb-3 p-3 rounded bg-red-50 text-red-700 text-sm">{{ session('error') }}</div>
            @endif

            <form action="{{ route('monitoring.evaluasi', $kegiatan) }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    {{-- Status --}}
                    <div class="flex flex-col">
                        <label class="text-xs text-gray-500 mb-1">Status</label>
                        <select name="status_evaluasi" class="form-minimal" required>
                            <option value="on_track">On Track</option>
                            <option value="terlambat">Terlambat</option>
                            <option value="tidak_sesuai">Tidak Sesuai</option>
                        </select>
                    </div>

                    {{-- Tanggal --}}
                    <div class="flex flex-col">
                        <label class="text-xs text-gray-500 mb-1">Tanggal</label>
                        <input type="date" name="tanggal_evaluasi" class="form-minimal"
                            value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    {{-- Rekomendasi --}}
                    <div class="flex flex-col">
                        <label class="text-xs text-gray-500 mb-1">Rekomendasi</label>
                        <input type="text" name="rekomendasi" class="form-minimal" placeholder="Opsional...">
                    </div>
                </div>

                {{-- Arahan --}}
                <div class="flex flex-col">
                    <label class="text-xs text-gray-500 mb-1">Arahan</label>
                    <textarea name="catatan_evaluasi" rows="3" class="form-minimal resize-none"
                        placeholder="Tuliskan evaluasi atau arahan..." required></textarea>
                    <p class="text-[11px] text-gray-400 mt-1">Minimal 10 karakter.</p>
                </div>

                <div class="flex justify-end pt-3">
                    <button type="submit" class="btn-minimal flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
        @endif
        @endauth
    </div>
</x-app-layout>