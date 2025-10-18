<x-app-layout>
    <div class="max-w-3xl mx-auto space-y-6 px-4">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Edit Rincian</h1>
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

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <form action="{{ route('rincian.update', [$subkegiatan, $rincian]) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Uraian</label>
                        <input type="text" name="uraian" value="{{ old('uraian', $rincian->uraian) }}"
                            class="w-full mt-1 border-gray-300 rounded-md text-sm" required>
                        @error('uraian') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kategori</label>
                        <select name="kategori" class="w-full mt-1 border-gray-300 rounded-md text-sm">
                            <option value="">- pilih -</option>
                            @foreach(['pengadaan_langsung','swakelola','pokir'] as $k)
                            <option value="{{ $k }}" {{ old('kategori', $rincian->kategori) === $k ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_',' ',$k)) }}
                            </option>
                            @endforeach
                        </select>
                        @error('kategori') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Anggaran (Rp)</label>
                        <input type="number" min="0" name="anggaran" value="{{ old('anggaran', $rincian->anggaran) }}"
                            class="w-full mt-1 border-gray-300 rounded-md text-sm">
                        @error('anggaran') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('subkegiatan.rincian.index', $subkegiatan) }}"
                        class="px-3 py-2 bg-gray-200 text-sm rounded-md">Batal</a>
                    <button class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-md">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>