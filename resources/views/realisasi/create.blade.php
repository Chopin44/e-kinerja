<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-6 px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Input Realisasi Baru</h1>
                    <p class="text-gray-500 mt-1 text-sm">Masukkan data realisasi fisik & anggaran kegiatan</p>
                </div>
                <a href="{{ route('realisasi.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <form action="{{ route('realisasi.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">
                    <!-- Pilih Kegiatan -->
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Kegiatan</label>
                        <select name="kegiatan_id"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                            <option value="">Pilih Kegiatan</option>
                            @foreach($kegiatans as $kegiatan)
                            <option value="{{ $kegiatan->id }}" {{ old('kegiatan_id')==$kegiatan->id ? 'selected' : ''
                                }}>
                                {{ $kegiatan->nama }} - {{ $kegiatan->bidang->nama }}
                            </option>
                            @endforeach
                        </select>
                        @error('kegiatan_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Realisasi Fisik -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Realisasi Fisik (%)</label>
                        <input type="number" name="realisasi_fisik" min="0" max="100"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="0-100" value="{{ old('realisasi_fisik') }}" required>
                        @error('realisasi_fisik')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Realisasi Anggaran -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Realisasi Anggaran (Rp)</label>
                        <input type="number" name="realisasi_anggaran" min="0"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="Masukkan nominal anggaran" value="{{ old('realisasi_anggaran') }}" required>
                        @error('realisasi_anggaran')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Tanggal Realisasi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Realisasi</label>
                        <input type="date" name="tanggal_realisasi"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            value="{{ old('tanggal_realisasi') }}" required>
                        @error('tanggal_realisasi')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Lokasi -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Lokasi</label>
                        <input type="text" name="lokasi"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="Masukkan lokasi kegiatan" value="{{ old('lokasi') }}">
                        @error('lokasi')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Catatan -->
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Catatan</label>
                        <textarea name="catatan" rows="3"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="Catatan tambahan">{{ old('catatan') }}</textarea>
                        @error('catatan')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Dokumen -->
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Upload Dokumen</label>
                        <input type="file" name="dokumen[]" multiple
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600">
                        <p class="text-xs text-gray-500 mt-1">Format: PDF, JPG, PNG, DOC, DOCX. Maks 10MB per file.</p>
                        @error('dokumen.*')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Tombol -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100 mt-4">
                    <a href="{{ route('realisasi.index') }}"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                        <i class="fas fa-times mr-1"></i> Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                        <i class="fas fa-save mr-1"></i> Simpan Realisasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>