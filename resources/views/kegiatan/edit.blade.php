<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-6 px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Edit Kegiatan</h1>
                    <p class="text-gray-500 mt-1 text-sm">Perbarui detail kegiatan yang sudah ada</p>
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
                        @error('nama')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Bidang -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Bidang</label>
                        <select name="bidang_id"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                            @foreach($bidangs as $bidang)
                            <option value="{{ $bidang->id }}" {{ old('bidang_id', $kegiatan->bidang_id) == $bidang->id ?
                                'selected' : '' }}>
                                {{ $bidang->nama }}
                            </option>
                            @endforeach
                        </select>
                        @error('bidang_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Penanggung Jawab -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Penanggung Jawab</label>
                        <select name="user_id"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $kegiatan->user_id) == $user->id ?
                                'selected' : '' }}>
                                {{ $user->name }} - {{ $user->bidang->nama }}
                            </option>
                            @endforeach
                        </select>
                        @error('user_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Kategori -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kategori</label>
                        <select name="kategori"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                            <option value="pengadaan_langsung" {{ old('kategori', $kegiatan->kategori) ==
                                'pengadaan_langsung' ? 'selected' : '' }}>Pengadaan Langsung</option>
                            <option value="swakelola" {{ old('kategori', $kegiatan->kategori) ==
                                'swakelola' ? 'selected' : '' }}>Swakelola</option>
                            <option value="pokir" {{ old('kategori', $kegiatan->kategori) ==
                                'pokir' ? 'selected' : '' }}>Pokir</option>
            
                        </select>
                        @error('kategori')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Periode -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Periode</label>
                        <select name="periode_type"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                            <option value="tahunan" {{ old('periode_type', $kegiatan->periode_type) == 'tahunan' ?
                                'selected' : '' }}>Tahunan</option>
                            <option value="bulanan" {{ old('periode_type', $kegiatan->periode_type) == 'bulanan' ?
                                'selected' : '' }}>Bulanan</option>
                            <option value="triwulan" {{ old('periode_type', $kegiatan->periode_type) == 'triwulan' ?
                                'selected' : '' }}>Triwulan</option>
                        </select>
                        @error('periode_type')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Tahun -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tahun</label>
                        <select name="tahun"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                            @for($year = date('Y'); $year <= date('Y') + 5; $year++) <option value="{{ $year }}" {{
                                old('tahun', $kegiatan->tahun) == $year ? 'selected' : '' }}>
                                {{ $year }}
                                </option>
                                @endfor
                        </select>
                        @error('tahun')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Target Fisik -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Target Fisik (%)</label>
                        <input type="number" name="target_fisik" min="0" max="100"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            value="{{ old('target_fisik', $kegiatan->target_fisik) }}" required>
                        @error('target_fisik')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Target Anggaran -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Target Anggaran (Rp)</label>
                        <input type="number" name="target_anggaran" min="0"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            value="{{ old('target_anggaran', $kegiatan->target_anggaran) }}" required>
                        @error('target_anggaran')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Tanggal Mulai -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            value="{{ old('tanggal_mulai', $kegiatan->tanggal_mulai->format('Y-m-d')) }}" required>
                        @error('tanggal_mulai')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Tanggal Selesai -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            value="{{ old('tanggal_selesai', $kegiatan->tanggal_selesai->format('Y-m-d')) }}" required>
                        @error('tanggal_selesai')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                            <option value="draft" {{ old('status', $kegiatan->status) == 'draft' ? 'selected' : ''
                                }}>Draft</option>
                            <option value="aktif" {{ old('status', $kegiatan->status) == 'aktif' ? 'selected' : ''
                                }}>Aktif</option>
                            <option value="selesai" {{ old('status', $kegiatan->status) == 'selesai' ? 'selected' : ''
                                }}>Selesai</option>
                        </select>
                        @error('status')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Deskripsi -->
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea name="deskripsi" rows="3"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="Deskripsi kegiatan">{{ old('deskripsi', $kegiatan->deskripsi) }}</textarea>
                        @error('deskripsi')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
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