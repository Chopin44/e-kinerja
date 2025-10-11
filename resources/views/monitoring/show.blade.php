<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                        <i class="fas fa-clipboard-list text-blue-600"></i> Detail Monitoring Kegiatan
                    </h1>
                    <p class="text-gray-600 mt-1">{{ $kegiatan->nama }}</p>
                </div>
                <a href="{{ route('monitoring.index') }}"
                    class="px-3 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>

        <!-- Ringkasan -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md-grid-cols-2 md:grid-cols-2 gap-6">
                    <div>
                        <div class="text-sm text-gray-600">Bidang</div>
                        <div class="font-semibold">{{ $kegiatan->bidang->nama ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Staf Admin</div>
                        <div class="font-semibold">{{ $kegiatan->user->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Periode</div>
                        <div class="font-semibold">{{ $kegiatan->periode ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Target Fisik</div>
                        <div class="font-semibold">{{ $kegiatan->target_fisik }}%</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Target Anggaran</div>
                        <div class="font-semibold">Rp {{ number_format((float)$kegiatan->target_anggaran,0,',','.') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Status Evaluasi</div>
                        <div class="font-semibold capitalize">{{ str_replace('_',' ', $status_evaluasi ?? '-') }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div class="border rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-700">Progress Fisik Saat Ini</h3>
                            <span class="text-sm font-bold text-gray-900">{{ $current_progress }}%</span>
                        </div>
                        <div class="progress-bar mt-2">
                            <div class="progress-fill" style="width: {{ (float)$current_progress }}%"></div>
                        </div>
                    </div>

                    <div class="border rounded-lg p-4">
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

            <!-- Evaluasi Terakhir -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Evaluasi Terakhir</h3>
                @if($latestEvaluation)
                <div class="space-y-2 text-sm">
                    <div><span class="text-gray-500">Tanggal:</span> {{
                        optional($latestEvaluation->tanggal_evaluasi)->format('d/m/Y') }}</div>
                    <div><span class="text-gray-500">Status:</span> {{ str_replace('_',' ',
                        $latestEvaluation->status_evaluasi) }}</div>
                    <div><span class="text-gray-500">Evaluator:</span> {{ $latestEvaluation->evaluator->name ?? '-' }}
                    </div>
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

        <!-- Timeline Progress -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Timeline Progress</h3>
            @if(count($timeline))
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fisik (%)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Anggaran (Rp)
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($timeline as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm">{{ $row['tanggal'] }}</td>
                            <td class="px-6 py-3 text-sm">{{ $row['progress_fisik'] }}</td>
                            <td class="px-6 py-3 text-sm">Rp {{
                                number_format((float)$row['progress_anggaran'],0,',','.') }}</td>
                            <td class="px-6 py-3 text-sm">{{ $row['lokasi'] ?? '-' }}</td>
                            <td class="px-6 py-3 text-sm">{{ ucfirst($row['status']) }}</td>
                            <td class="px-6 py-3 text-sm">{{ $row['catatan'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-sm text-gray-500">Belum ada realisasi tercatat.</div>
            @endif
        </div>

        {{-- Form Evaluasi — hanya untuk ADMIN --}}
        @auth
        @if(auth()->user()->role === 'admin')
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Input Arahan</h3>

            {{-- Flash --}}
            @if(session('success'))
            <div class="mb-3 p-3 rounded bg-green-50 text-green-700 text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="mb-3 p-3 rounded bg-red-50 text-red-700 text-sm">{{ session('error') }}</div>
            @endif

            <form action="{{ route('monitoring.evaluasi', $kegiatan) }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status Evaluasi</label>
                        <select name="status_evaluasi" class="form-select" required>
                            <option value="on_track" {{ old('status_evaluasi')==='on_track' ? 'selected' :'' }}>On Track
                            </option>
                            <option value="terlambat" {{ old('status_evaluasi')==='terlambat' ? 'selected' :'' }}>
                                Terlambat</option>
                            <option value="tidak_sesuai" {{ old('status_evaluasi')==='tidak_sesuai' ? 'selected' :'' }}>
                                Tidak Sesuai</option>
                        </select>
                        @error('status_evaluasi')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Evaluasi</label>
                        <input type="date" name="tanggal_evaluasi" class="form-input"
                            value="{{ old('tanggal_evaluasi', now()->format('Y-m-d')) }}" required>
                        @error('tanggal_evaluasi')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                    </div>

                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Arahan</label>
                    <textarea name="catatan_evaluasi" rows="4" class="form-input" required
                        placeholder="Tuliskan evaluasi atau arahan...">{{ old('catatan_evaluasi') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Minimal 10 karakter.</p>
                    @error('catatan_evaluasi')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-2"></i> Simpan Evaluasi
                    </button>
                </div>
            </form>
        </div>
        @endif
        @endauth
    </div>


</x-app-layout>