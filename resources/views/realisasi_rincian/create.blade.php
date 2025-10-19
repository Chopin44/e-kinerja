<x-app-layout>
    <div class="max-w-3xl mx-auto space-y-6 px-4">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h1 class="text-xl font-semibold">Input Realisasi Rincian</h1>
            <p class="text-sm text-gray-500 mt-1">
                Subkegiatan: <b>{{ $rincian->subKegiatan->nama }}</b> — Rincian: <b>{{ $rincian->uraian }}</b>
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <form action="{{ route('rincian.realisasi-rincian.store', $rincian) }}" method="POST" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Realisasi Anggaran (Rp)</label>
                        <input type="number" name="realisasi_anggaran" min="0" step="0.01"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            value="{{ old('realisasi_anggaran') }}" required>
                        @error('realisasi_anggaran')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Realisasi</label>
                        <input type="date" name="tanggal_realisasi"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            value="{{ old('tanggal_realisasi') }}" required>
                        @error('tanggal_realisasi')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lokasi</label>
                        <input type="text" name="lokasi"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            value="{{ old('lokasi') }}">
                        @error('lokasi')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Catatan</label>
                        <textarea name="catatan" rows="3"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="Catatan tambahan (opsional)">{{ old('catatan') }}</textarea>
                        @error('catatan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t">
                    <a href="{{ route('subkegiatan.rincian.index', $rincian->subKegiatan) }}"
                        class="inline-flex items-center h-9 px-3 text-xs font-medium rounded-md bg-gray-100 text-gray-800 hover:bg-gray-200">
                        Batal
                    </a>
                    <button type="submit"
                        class="inline-flex items-center h-9 px-3 text-xs font-medium rounded-md bg-green-600 text-white hover:bg-green-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>