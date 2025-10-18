<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-6 px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Edit Kegiatan</h1>
                    <p class="text-gray-500 mt-1 text-sm">Perbarui detail umum kegiatan</p>
                </div>
                <a href="{{ route('kegiatan.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <form action="{{ route('kegiatan.update', $kegiatan) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">

                    <!-- Nama Kegiatan -->
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Nama Kegiatan</label>
                        <input type="text" name="nama"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            value="{{ old('nama', $kegiatan->nama) }}" required>
                    </div>

                    <!-- BIDANG -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Bidang</label>

                        @if(Auth::user()->hasRole('admin'))
                        <select name="bidang_id"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                            @foreach($bidangs as $bidang)
                            <option value="{{ $bidang->id }}" {{ old('bidang_id', $kegiatan->bidang_id) == $bidang->id ?
                                'selected' : '' }}
                                >
                                {{ $bidang->nama }}
                            </option>
                            @endforeach
                        </select>
                        @else
                        <input type="text"
                            value="{{ $kegiatan->bidang->nama ?? Auth::user()->bidang->nama ?? 'Tidak Ada Bidang' }}"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md bg-gray-100 cursor-not-allowed"
                            readonly>
                        <input type="hidden" name="bidang_id"
                            value="{{ $kegiatan->bidang_id ?? Auth::user()->bidang_id }}">
                        @endif
                    </div>


                    <!-- Periode (pakai spasi: "triwulan 1" dst untuk konsisten dengan validasi) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Periode</label>
                        <select name="periode_type"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                            @foreach (['triwulan 1','triwulan 2','triwulan 3','triwulan 4'] as $p)
                            <option value="{{ $p }}" {{ old('periode_type', $kegiatan->periode_type) === $p ? 'selected'
                                : '' }}
                                >
                                {{ ucfirst($p) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tahun -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tahun</label>
                        <select name="tahun"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                            @for($year = date('Y'); $year <= date('Y') + 5; $year++) <option value="{{ $year }}" {{
                                old('tahun', $kegiatan->tahun) == $year ? 'selected' : '' }}
                                >
                                {{ $year }}
                                </option>
                                @endfor
                        </select>
                    </div>

                    <!-- Tanggal Mulai -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            value="{{ old('tanggal_mulai', optional($kegiatan->tanggal_mulai)->format('Y-m-d')) }}"
                            required>
                    </div>

                    <!-- Tanggal Selesai -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            value="{{ old('tanggal_selesai', optional($kegiatan->tanggal_selesai)->format('Y-m-d')) }}"
                            required>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                            @foreach (['draft'=>'Draft','aktif'=>'Aktif','selesai'=>'Selesai'] as $val=>$label)
                            <option value="{{ $val }}" {{ old('status', $kegiatan->status) == $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Deskripsi -->
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea name="deskripsi" rows="3"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="Deskripsi kegiatan">{{ old('deskripsi', $kegiatan->deskripsi) }}</textarea>
                    </div>
                </div>

                <!-- Catatan -->
                <div class="rounded-md bg-amber-50 border border-amber-200 text-amber-800 text-xs p-3">
                    <b>Catatan:</b> Target fisik, target anggaran, dan kategori sekarang dikelola pada
                    <b>Subkegiatan/Rincian</b>. Silakan ubah di halaman Subkegiatan terkait.
                </div>

                <!-- Tombol -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100 mt-4">
                    <a href="{{ route('kegiatan.index') }}"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                        <i class="fas fa-times mr-1"></i> Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                        <i class="fas fa-save mr-1"></i> Update Kegiatan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>