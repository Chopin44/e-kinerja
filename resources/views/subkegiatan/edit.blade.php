<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6 px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Edit Subkegiatan</h1>
                    <p class="text-gray-500 text-sm mt-1">{{ $subkegiatan->nama }}</p>
                </div>
                <a href="{{ route('kegiatan.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <form action="{{ route('subkegiatan.update', $subkegiatan) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Kegiatan --}}
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Kegiatan</label>
                        <select name="kegiatan_id" class="w-full mt-1 border-gray-300 rounded-md text-sm" required>
                            @foreach($kegiatans as $k)
                            <option value="{{ $k->id }}" {{ old('kegiatan_id', $subkegiatan->kegiatan_id) == $k->id ?
                                'selected' : '' }}>
                                {{ $k->nama }} — {{ $k->bidang->nama ?? '-' }} ({{ $k->tahun }})
                            </option>
                            @endforeach
                        </select>
                        @error('kegiatan_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Staf Admin --}}
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Staf Admin</label>
                        <select name="user_id" class="w-full mt-1 border-gray-300 rounded-md text-sm">
                            @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ old('user_id', $subkegiatan->user_id) == $u->id ? 'selected'
                                : '' }}>
                                {{ $u->name }} {{ $u->bidang ? '— '.$u->bidang->nama : '' }}
                            </option>
                            @endforeach
                        </select>
                        @error('user_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Nama Subkegiatan --}}
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Nama Subkegiatan</label>
                        <input type="text" name="nama" value="{{ old('nama', $subkegiatan->nama) }}"
                            class="w-full mt-1 border-gray-300 rounded-md text-sm" required>
                        @error('nama') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Target Anggaran --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Target Anggaran (Rp)</label>
                        <input type="number" min="0" name="target_anggaran"
                            value="{{ old('target_anggaran', $subkegiatan->target_anggaran) }}"
                            class="w-full mt-1 border-gray-300 rounded-md text-sm" required>
                        @error('target_anggaran') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Target Fisik --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Target Fisik (%)</label>
                        <input type="number" min="0" max="100" step="0.1" name="target_fisik"
                            value="{{ old('target_fisik', $subkegiatan->target_fisik ?? 0) }}"
                            class="w-full mt-1 border-gray-300 rounded-md text-sm" placeholder="Misal: 75.5">
                        @error('target_fisik') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Periode --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Periode</label>
                        <select name="periode_type" class="w-full mt-1 border-gray-300 rounded-md text-sm">
                            <option value="">Ikuti Kegiatan</option>
                            @foreach(['triwulan 1','triwulan 2','triwulan 3','triwulan 4'] as $p)
                            <option value="{{ $p }}" {{ old('periode_type', $subkegiatan->periode_type) === $p ?
                                'selected' : '' }}>
                                {{ ucfirst($p) }}
                            </option>
                            @endforeach
                        </select>
                        @error('periode_type') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tahun --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tahun</label>
                        <input type="number" name="tahun" value="{{ old('tahun', $subkegiatan->tahun) }}"
                            class="w-full mt-1 border-gray-300 rounded-md text-sm">
                        @error('tahun') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea name="deskripsi" rows="3"
                            class="w-full mt-1 border-gray-300 rounded-md text-sm">{{ old('deskripsi', $subkegiatan->deskripsi) }}</textarea>
                        @error('deskripsi') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex justify-between items-center">
                    <a href="{{ route('subkegiatan.rincian.index', $subkegiatan) }}"
                        class="text-blue-600 text-sm hover:underline">
                        Kelola Rincian Subkegiatan →
                    </a>
                    <div class="flex gap-2">
                        <a href="{{ route('kegiatan.index') }}"
                            class="px-3 py-2 bg-gray-200 text-sm rounded-md">Batal</a>
                        <button class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-md">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>