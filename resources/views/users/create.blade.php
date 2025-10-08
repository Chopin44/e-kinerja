<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-6 px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Tambah Pengguna Baru</h1>
                    <p class="text-gray-500 mt-1 text-sm">Masukkan data akun dan hak akses pengguna</p>
                </div>
                <a href="{{ route('users.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">
                    <!-- Nama Lengkap -->
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="name"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="Masukkan nama pengguna" value="{{ old('name') }}" required>
                    </div>

                    <!-- NIP -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">NIP</label>
                        <input type="text" name="nip"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="Masukkan NIP" value="{{ old('nip') }}">
                    </div>

                    <!-- Nomor HP -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nomor HP</label>
                        <input type="text" name="phone"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="Masukkan nomor HP" value="{{ old('phone') }}">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="Alamat email pengguna" value="{{ old('email') }}" required>
                    </div>

                    <!-- Bidang -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Bidang</label>
                        <select name="bidang_id"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600">
                            <option value="">Pilih Bidang</option>
                            @foreach($bidangs as $bidang)
                            <option value="{{ $bidang->id }}" {{ old('bidang_id')==$bidang->id ? 'selected' : '' }}>
                                {{ $bidang->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Role / Hak Akses</label>
                        <select name="role"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                            <option value="">Pilih Role</option>
                            <option value="admin" {{ old('role')=='admin' ? 'selected' : '' }}>Admin</option>
                            <option value="staf" {{ old('role')=='staf' ? 'selected' : '' }}>Staf</option>
                            <option value="pimpinan" {{ old('role')=='pimpinan' ? 'selected' : '' }}>Pimpinan</option>
                        </select>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="Minimal 6 karakter" required>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="Ulangi password" required>
                    </div>
                </div>

                <!-- Tombol -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100 mt-4">
                    <a href="{{ route('users.index') }}"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                        <i class="fas fa-times mr-1"></i> Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                        <i class="fas fa-save mr-1"></i> Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>