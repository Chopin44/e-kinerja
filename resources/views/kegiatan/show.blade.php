<x-app-layout>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow p-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-eye text-blue-600 mr-3"></i>
                    Detail Kegiatan
                </h1>
                <p class="text-gray-600 mt-1">Informasi lengkap tentang kegiatan yang dipilih</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('kegiatan.index') }}" class="btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
                @can('update', $kegiatan)
                <a href="{{ route('kegiatan.edit', $kegiatan) }}" class="btn-warning">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                @endcan
            </div>
        </div>

        <!-- Detail Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nama Kegiatan</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $kegiatan->nama }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Bidang</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $kegiatan->bidang->nama }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Penanggung Jawab</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $kegiatan->user->name }} ({{ $kegiatan->user->bidang->nama
                        ?? '-' }})</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Kategori</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $kegiatan->kategori)) }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Periode</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($kegiatan->periode_type) }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Tahun</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $kegiatan->tahun }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Tanggal Mulai</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($kegiatan->tanggal_mulai)->format('d
                        M Y') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Tanggal Selesai</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{
                        \Carbon\Carbon::parse($kegiatan->tanggal_selesai)->format('d M Y') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Target Fisik</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $kegiatan->target_fisik }} %</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Target Anggaran</dt>
                    <dd class="mt-1 text-sm text-gray-900">Rp {{ number_format($kegiatan->target_anggaran, 0, ',', '.')
                        }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Progress Fisik</dt>
                    <dd class="mt-2">
                        <div class="flex justify-between text-xs text-gray-600 mb-1">
                            <span>{{ number_format($kegiatan->current_progress, 1) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $kegiatan->current_progress }}%">
                            </div>
                        </div>
                    </dd>
                </div>

                <div>
                    @php
                    $budgetProgress = $kegiatan->target_anggaran > 0
                    ? ($kegiatan->current_budget_realization / $kegiatan->target_anggaran) * 100
                    : 0;
                    @endphp
                    <dt class="text-sm font-medium text-gray-500">Progress Anggaran</dt>
                    <dd class="mt-2">
                        <div class="flex justify-between text-xs text-gray-600 mb-1">
                            <span>{{ number_format($budgetProgress, 1) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $budgetProgress }}%"></div>
                        </div>
                    </dd>
                </div>

                <div class="md:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Deskripsi</dt>
                    <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $kegiatan->deskripsi ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Status Evaluasi</dt>
                    @php
                    $statusClass = 'status-' . str_replace('_', '-', $kegiatan->status_evaluasi);
                    @endphp
                    <dd class="mt-1">
                        <span class="status-badge {{ $statusClass }}">
                            {{ ucfirst(str_replace('_', ' ', $kegiatan->status_evaluasi)) }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</x-app-layout>