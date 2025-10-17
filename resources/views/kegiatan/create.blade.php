<!-- resources/views/kegiatan/create.blade.php -->
<x-app-layout>


    <div class="max-w-5xl mx-auto space-y-6 px-4" x-data="{ mode: '{{ old('mode','baru') }}' }">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Tambah Kegiatan / Subkegiatan</h1>
                    <p class="text-gray-500 mt-1 text-sm">Buat kegiatan baru atau tambahkan subkegiatan ke kegiatan yang
                        sudah ada</p>
                </div>
                <a href="{{ route('kegiatan.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <form action="{{ route('kegiatan.store') }}" method="POST" class="space-y-8">
                @csrf

                {{-- MODE INPUT --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mode Input</label>
                    <div class="flex gap-6">
                        <label class="inline-flex items-center">
                            <input type="radio" name="mode" value="baru" class="mr-2" x-model="mode">
                            <span>Buat Kegiatan Baru</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="mode" value="existing" class="mr-2" x-model="mode">
                            <span>Tambahkan ke Kegiatan yang Sudah Ada</span>
                        </label>
                    </div>

                    <!-- Pilih Kegiatan Existing -->
                    <div class="mt-4" x-show="mode==='existing'">
                        <label class="block text-sm font-medium text-gray-700">Pilih Kegiatan</label>
                        <select name="existing_kegiatan_id" class="form-select">
                            <option value="">-- Pilih Kegiatan --</option>
                            @foreach($kegiatansExisting as $k)
                            <option value="{{ $k->id }}" {{ old('existing_kegiatan_id')==$k->id ? 'selected':'' }}>
                                {{ $k->nama }}
                            </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Jika tidak ada di daftar, pilih mode "Buat Kegiatan Baru".
                        </p>
                    </div>
                </div>

                {{-- ================== FORM KEGIATAN (HANYA JIKA mode=baru) ================== --}}
                <div x-show="mode==='baru'">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">
                        <!-- Nama Kegiatan -->
                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Nama Kegiatan</label>
                            <input type="text" name="nama" class="form-input" placeholder="Masukkan nama kegiatan"
                                value="{{ old('nama') }}">
                        </div>

                        {{-- === BIDANG (PJ Level Kegiatan) === --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Bidang</label>
                            @if(Auth::user()->hasRole('admin'))
                            <select name="bidang_id" class="form-select">
                                <option value="">Pilih Bidang</option>
                                @foreach($bidangs as $bidang)
                                <option value="{{ $bidang->id }}" {{ old('bidang_id')==$bidang->id ? 'selected':'' }}>
                                    {{ $bidang->nama }}
                                </option>
                                @endforeach
                            </select>
                            @else
                            <input type="text" value="{{ Auth::user()->bidang->nama ?? 'Tidak Ada Bidang' }}"
                                class="form-input bg-gray-100 cursor-not-allowed" readonly>
                            <input type="hidden" name="bidang_id" value="{{ Auth::user()->bidang_id }}">
                            @endif
                        </div>


                        <!-- Periode -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Periode</label>
                            <select name="periode_type" class="form-select">
                                <option value="triwulan 1" {{ old('periode_type')=='triwulan 1' ? 'selected' : '' }}>
                                    Triwulan 1</option>
                                <option value="triwulan 2" {{ old('periode_type')=='triwulan 2' ? 'selected' : '' }}>
                                    Triwulan 2</option>
                                <option value="triwulan 3" {{ old('periode_type')=='triwulan 3' ? 'selected' : '' }}>
                                    Triwulan 3</option>
                                <option value="triwulan 4" {{ old('periode_type')=='triwulan 4' ? 'selected' : '' }}>
                                    Triwulan 4</option>
                            </select>
                        </div>

                        <!-- Tahun -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tahun</label>
                            <select name="tahun" class="form-select">
                                @for($year = date('Y'); $year <= date('Y') + 5; $year++) <option value="{{ $year }}" {{
                                    old('tahun', date('Y'))==$year ? 'selected' : '' }}>
                                    {{ $year }}
                                    </option>
                                    @endfor
                            </select>
                        </div>

                        <!-- Tanggal Mulai -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-input"
                                value="{{ old('tanggal_mulai') }}">
                        </div>

                        <!-- Tanggal Selesai -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-input"
                                value="{{ old('tanggal_selesai') }}">
                        </div>

                        <!-- Deskripsi -->
                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="deskripsi" rows="3" class="form-textarea"
                                placeholder="Deskripsi kegiatan">{{ old('deskripsi') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ================== SUBKEGIATAN & RINCIAN (UNTUK DUA MODE) ================== --}}
                <div x-data="subForm({
                    tahunDefault: {{ old('tahun', date('Y')) }},
                    periodeDefault: '{{ old('periode_type', 'triwulan 1') }}',
                })">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-lg font-semibold text-gray-800">Subkegiatan</h2>
                        <button type="button" @click="addSub()" class="btn-secondary">
                            <i class="fas fa-plus mr-1"></i> Tambah Subkegiatan
                        </button>
                    </div>

                    <template x-if="subs.length === 0">
                        <p class="text-sm text-gray-500">Belum ada subkegiatan. Klik <b>Tambah Subkegiatan</b>.</p>
                    </template>

                    <div class="space-y-6">
                        <template x-for="(sub, si) in subs" :key="si">
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <div class="flex items-start justify-between">
                                    <h3 class="font-medium text-gray-800">Subkegiatan <span x-text="si+1"></span></h3>
                                    <button type="button" @click="removeSub(si)"
                                        class="text-red-600 text-sm hover:underline">
                                        Hapus
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-3">
                                    <div class="lg:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Nama Subkegiatan</label>
                                        <input type="text" class="form-input" :name="`subkegiatans[${si}][nama]`"
                                            x-model="sub.nama">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Kode Subkegiatan
                                            (opsional)</label>
                                        <input type="text" class="form-input"
                                            :name="`subkegiatans[${si}][kode_subkegiatan]`"
                                            x-model="sub.kode_subkegiatan">
                                    </div>

                                    {{-- === STAF ADMIN (PJ Subkegiatan) === --}}
                                    <div class="lg:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Staf Admin
                                            (Subkegiatan)</label>

                                        @if(Auth::user()->hasRole('admin'))
                                        <select class="form-select" :name="`subkegiatans[${si}][user_id]`"
                                            x-model="sub.user_id">
                                            <option value="">Pilih Staf Admin</option>
                                            @foreach($users as $u)
                                            <option value="{{ $u->id }}">
                                                {{ $u->name }} {{ $u->bidang? ' - '.$u->bidang->nama : '' }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @else
                                        <input type="text" value="{{ Auth::user()->name }}"
                                            class="form-input bg-gray-100 cursor-not-allowed" readonly>
                                        <input type="hidden" :name="`subkegiatans[${si}][user_id]`"
                                            value="{{ Auth::id() }}">
                                        @endif
                                        <p class="text-xs text-gray-500 mt-1">Penanggung jawab khusus subkegiatan ini.
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Target Anggaran
                                            (Rp)</label>
                                        <input type="number" min="0" class="form-input"
                                            :name="`subkegiatans[${si}][target_anggaran]`"
                                            x-model="sub.target_anggaran">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Periode</label>
                                        <select class="form-select" :name="`subkegiatans[${si}][periode_type]`"
                                            x-model="sub.periode_type">
                                            <option value="">Ikuti Kegiatan</option>
                                            <option value="triwulan 1">Triwulan 1</option>
                                            <option value="triwulan 2">Triwulan 2</option>
                                            <option value="triwulan 3">Triwulan 3</option>
                                            <option value="triwulan 4">Triwulan 4</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tahun</label>
                                        <input type="number" class="form-input" :name="`subkegiatans[${si}][tahun]`"
                                            x-model="sub.tahun">
                                    </div>

                                    <div class="sm:col-span-2 lg:col-span-3">
                                        <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                                        <textarea rows="2" class="form-textarea"
                                            :name="`subkegiatans[${si}][deskripsi]`" x-model="sub.deskripsi"></textarea>
                                    </div>
                                </div>

                                {{-- ================== RINCIAN ================== --}}
                                <div class="mt-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-medium text-gray-700">Rincian Kegiatan</h4>
                                        <button type="button" @click="addRincian(si)" class="btn-secondary text-xs">
                                            <i class="fas fa-plus mr-1"></i> Tambah Rincian
                                        </button>
                                    </div>

                                    <template x-if="sub.rincian.length === 0">
                                        <p class="text-xs text-gray-500">Belum ada rincian.</p>
                                    </template>

                                    <div class="space-y-3">
                                        <template x-for="(r, ri) in sub.rincian" :key="ri">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                                                <div class="lg:col-span-2">
                                                    <label class="block text-xs text-gray-600">Uraian</label>
                                                    <input type="text" class="form-input"
                                                        :name="`subkegiatans[${si}][rincian][${ri}][uraian]`"
                                                        x-model="r.uraian">
                                                </div>

                                                <div>
                                                    <label class="block text-xs text-gray-600">Kategori</label>
                                                    <select class="form-select"
                                                        :name="`subkegiatans[${si}][rincian][${ri}][kategori]`"
                                                        x-model="r.kategori">
                                                        <option value="">- pilih -</option>
                                                        <option value="pengadaan_langsung">Pengadaan Langsung</option>
                                                        <option value="swakelola">Swakelola</option>
                                                        <option value="pokir">Pokir</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="block text-xs text-gray-600">Anggaran (Rp)</label>
                                                    <input type="number" min="0" class="form-input"
                                                        :name="`subkegiatans[${si}][rincian][${ri}][anggaran]`"
                                                        x-model="r.anggaran">
                                                </div>

                                                <div>
                                                    <label class="block text-xs text-gray-600">Satuan</label>
                                                    <input type="text" class="form-input"
                                                        :name="`subkegiatans[${si}][rincian][${ri}][satuan]`"
                                                        x-model="r.satuan" placeholder="paket/unit/meter">
                                                </div>

                                                <div>
                                                    <label class="block text-xs text-gray-600">Volume</label>
                                                    <input type="number" min="0" class="form-input"
                                                        :name="`subkegiatans[${si}][rincian][${ri}][volume]`"
                                                        x-model="r.volume">
                                                </div>

                                                <div class="text-right">
                                                    <button type="button" @click="removeRincian(si, ri)"
                                                        class="text-red-600 text-xs hover:underline">
                                                        Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- INIT SCRIPT --}}
                    <script>
                        function subForm({tahunDefault, periodeDefault}) {
            return {
                subs: [],
                addSub() {
                    this.subs.push({
                        nama: '',
                        kode_subkegiatan: '',
                        user_id: '{{ Auth::user()->hasRole('admin') ? '' : Auth::id() }}', // default utk non-admin
                        target_anggaran: '',
                        periode_type: '', // kosong = ikut kegiatan
                        tahun: tahunDefault,
                        deskripsi: '',
                        rincian: []
                    });
                },
                removeSub(i){ this.subs.splice(i, 1); },
                addRincian(i){
                    this.subs[i].rincian.push({
                        uraian: '',
                        kategori: '',
                        anggaran: '',
                        satuan: '',
                        volume: ''
                    });
                },
                removeRincian(i, j){
                    this.subs[i].rincian.splice(j, 1);
                }
            }
        }
                    </script>
                </div>


                <!-- Tombol -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100 mt-4">
                    <a href="{{ route('kegiatan.index') }}" class="btn-secondary">
                        <i class="fas fa-times mr-1"></i> Batal
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- AlpineJS (kalau belum dimuat di layout utama) --}}
    {{-- <script src="https://unpkg.com/alpinejs" defer></script> --}}
</x-app-layout>