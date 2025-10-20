<x-app-layout>
    <div class="max-w-3xl mx-auto space-y-6 px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Edit Rincian</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Subkegiatan: <b>{{ $subkegiatan->nama }}</b>
                    </p>
                </div>
                <a href="{{ route('subkegiatan.rincian.index', $subkegiatan) }}"
                    class="text-gray-600 hover:text-gray-900 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <form action="{{ route('rincian.update', $rincian) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <!-- Uraian -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Uraian</label>
                        <input type="text" name="uraian" value="{{ old('uraian', $rincian->uraian) }}"
                            class="form-input mt-1 w-full border-gray-300 rounded-md text-sm focus:ring-green-600 focus:border-green-600"
                            placeholder="Tuliskan uraian kegiatan" required>
                        @error('uraian')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kategori -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kategori</label>
                        <select name="kategori"
                            class="form-select mt-1 w-full border-gray-300 rounded-md text-sm focus:ring-green-600 focus:border-green-600">
                            <option value="">- Pilih -</option>
                            <option value="pengadaan_langsung" {{ old('kategori', $rincian->
                                kategori)=='pengadaan_langsung' ? 'selected' : '' }}>Pengadaan Langsung</option>
                            <option value="swakelola" {{ old('kategori', $rincian->kategori)=='swakelola' ? 'selected' :
                                '' }}>Swakelola</option>
                            <option value="pokir" {{ old('kategori', $rincian->kategori)=='pokir' ? 'selected' : ''
                                }}>Pokir</option>
                        </select>
                        @error('kategori')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Anggaran -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Anggaran (Rp)</label>
                        <input type="number" name="anggaran" min="0" value="{{ old('anggaran', $rincian->anggaran) }}"
                            class="form-input mt-1 w-full border-gray-300 rounded-md text-sm focus:ring-green-600 focus:border-green-600"
                            placeholder="Masukkan nominal anggaran">
                        @error('anggaran')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Target Fisik -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Target Fisik (%)</label>
                        <input type="number" name="target_fisik" min="0" max="100" step="0.1"
                            value="{{ old('target_fisik', $rincian->target_fisik ?? 0) }}"
                            class="form-input mt-1 w-full border-gray-300 rounded-md text-sm focus:ring-green-600 focus:border-green-600"
                            placeholder="Contoh: 75.5">
                        @error('target_fisik')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Tombol -->
                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 mt-4">
                    <a href="{{ route('subkegiatan.rincian.index', $subkegiatan) }}"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                        <i class="fas fa-times mr-1"></i> Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                        <i class="fas fa-save mr-1"></i> Update Rincian
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>