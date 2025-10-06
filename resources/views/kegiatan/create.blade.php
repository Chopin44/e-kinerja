<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-6 px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Tambah Kegiatan Baru</h1>
                    <p class="text-gray-500 mt-1 text-sm">Buat rencana kegiatan baru</p>
                </div>
                <a href="{{ route('kegiatan.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <form action="{{ route('kegiatan.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">
                    <!-- Nama Kegiatan -->
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Nama Kegiatan</label>
                        <input type="text" name="nama"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="Masukkan nama kegiatan" value="{{ old('nama') }}" required>
                    </div>

                    <!-- Bidang -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Bidang</label>
                        <select name="bidang_id"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                            <option value="">Pilih Bidang</option>
                            @foreach($bidangs as $bidang)
                            <option value="{{ $bidang->id }}" {{ old('bidang_id')==$bidang->id ? 'selected' : '' }}>
                                {{ $bidang->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Penanggung Jawab -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Penanggung Jawab</label>
                        <select name="user_id"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                            <option value="">Pilih Penanggung Jawab</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id')==$user->id ? 'selected' : '' }}>
                                {{ $user->name }} - {{ $user->bidang->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kategori -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kategori</label>
                        <select name="kategori"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                            <option value="">Pilih Kategori</option>
                            <option value="belanja_langsung" {{ old('kategori')=='belanja_langsung' ? 'selected' : ''
                                }}>Belanja Langsung</option>
                            <option value="belanja_operasional" {{ old('kategori')=='belanja_operasional' ? 'selected'
                                : '' }}>Belanja Operasional</option>
                            <option value="program_prioritas" {{ old('kategori')=='program_prioritas' ? 'selected' : ''
                                }}>Program Prioritas</option>
                            <option value="kegiatan_rutin" {{ old('kategori')=='kegiatan_rutin' ? 'selected' : '' }}>
                                Kegiatan Rutin</option>
                        </select>
                    </div>

                    <!-- Periode -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Periode</label>
                        <select name="periode_type"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                            <option value="tahunan" {{ old('periode_type')=='tahunan' ? 'selected' : '' }}>Tahunan
                            </option>
                            <option value="bulanan" {{ old('periode_type')=='bulanan' ? 'selected' : '' }}>Bulanan
                            </option>
                            <option value="triwulan" {{ old('periode_type')=='triwulan' ? 'selected' : '' }}>Triwulan
                            </option>
                        </select>
                    </div>

                    <!-- Tahun -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tahun</label>
                        <select name="tahun"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                            @for($year = date('Y'); $year <= date('Y') + 5; $year++) <option value="{{ $year }}" {{
                                old('tahun', date('Y'))==$year ? 'selected' : '' }}>
                                {{ $year }}
                                </option>
                                @endfor
                        </select>
                    </div>

                    <!-- Target Fisik -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Target Fisik (%)</label>
                        <input type="number" name="target_fisik" min="0" max="100"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="0-100" value="{{ old('target_fisik') }}" required>
                    </div>

                    <!-- Target Anggaran -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Target Anggaran (Rp)</label>
                        <input type="number" name="target_anggaran" min="0"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="Masukkan nominal anggaran" value="{{ old('target_anggaran') }}" required>
                    </div>

                    <!-- Tanggal Mulai -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            value="{{ old('tanggal_mulai') }}" required>
                    </div>

                    <!-- Tanggal Selesai -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            value="{{ old('tanggal_selesai') }}" required>
                    </div>

                    <!-- Deskripsi -->
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea name="deskripsi" rows="3"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="Deskripsi kegiatan">{{ old('deskripsi') }}</textarea>
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
                        <i class="fas fa-save mr-1"></i> Simpan Kegiatan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>