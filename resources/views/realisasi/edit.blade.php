{{-- resources/views/realisasi/edit.blade.php --}}
<x-app-layout>
    @php
    // siapkan rows untuk tabel rincian (saat edit & saat validasi gagal)
    $presetRows = old('rincians', $realisasi->realisasiRincians->map(fn($d)=>[
    'rincian_kegiatan_id' => $d->rincian_kegiatan_id,
    'realisasi_anggaran' => (float) $d->realisasi_anggaran,
    'realisasi_fisik' => $d->realisasi_fisik,
    'realisasi_volume' => $d->realisasi_volume,
    'tanggal_realisasi' => optional($d->tanggal_realisasi)->format('Y-m-d'),
    'lokasi' => $d->lokasi,
    'catatan' => $d->catatan,
    ])->values());

    // config untuk Alpine (master dropdown, preset dari DB & old())
    $cfg = [
    // master dropdowns
    'kegiatans' => $kegiatans->map(fn($k)=>['id'=>$k->id,'nama'=>$k->nama])->values(),
    'subs' => $subKegiatans->map(fn($s)=>['id'=>$s->id,'kegiatan_id'=>$s->kegiatan_id,'nama'=>$s->nama])->values(),
    'rinciansMaster' => $rincians->map(fn($r)=>[
    'id' => $r->id,
    'sub_kegiatan_id' => $r->sub_kegiatan_id,
    'uraian' => $r->uraian,
    'anggaran' => $r->anggaran,
    'kategori' => $r->kategori,
    ])->values(),

    // label fallback untuk opsi yang mungkin tidak ada di list
    'dbKegiatanNama' => optional($realisasi->kegiatan)->nama,
    'dbSubNama' => optional($realisasi->subKegiatan)->nama,

    // preset dari DB
    'dbKegiatan' => $realisasi->kegiatan_id,
    'dbSub' => $realisasi->sub_kegiatan_id,
    'dbTanggal' => optional($realisasi->tanggal_realisasi)->format('Y-m-d'),
    'dbLokasi' => $realisasi->lokasi,
    'dbCatatan' => $realisasi->catatan,
    'dbFisik' => $realisasi->realisasi_fisik,

    // preset dari old()
    'presetKegiatan' => old('kegiatan_id'),
    'presetSub' => old('sub_kegiatan_id'),
    'presetTanggal' => old('tanggal_realisasi'),
    'presetLokasi' => old('lokasi', ''),
    'presetCatatan' => old('catatan', ''),
    'presetFisik' => old('realisasi_fisik'),
    'presetRows' => $presetRows,
    ];
    @endphp

    <div class="max-w-5xl mx-auto space-y-6 px-4" x-data="realisasiEditForm(@js($cfg))">
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Edit Realisasi</h1>
                    <p class="text-gray-500 mt-1 text-sm">
                        Perbarui realisasi <b>Subkegiatan</b> beserta <b>Rincian</b>-nya.
                    </p>
                </div>
                <a href="{{ route('realisasi.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        {{-- Form --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <form action="{{ route('realisasi.update', $realisasi) }}" method="POST" enctype="multipart/form-data"
                class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">
                    {{-- Kegiatan --}}
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">
                            Kegiatan <span class="text-red-500">*</span>
                        </label>

                        <select name="kegiatan_id" x-model="selectedKegiatan" @change="onKegiatanChange()"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                            <option value="">— Pilih Kegiatan —</option>

                            {{-- Fallback jika kegiatan terpilih tidak ada di list --}}
                            <template
                                x-if="selectedKegiatan && !kegiatans.some(k => String(k.id) === String(selectedKegiatan))">
                                <option :value="String(selectedKegiatan)"
                                    x-text="dbKegiatanNama || '(Kegiatan saat ini)'"></option>
                            </template>

                            <template x-for="k in kegiatans" :key="k.id">
                                <option :value="String(k.id)" x-text="k.nama"></option>
                            </template>
                        </select>

                        @error('kegiatan_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Subkegiatan --}}
                    <div class="sm:col-span-2 lg:col-span-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">
                                Subkegiatan <span class="text-red-500">*</span>
                            </label>
                            <span class="text-xs text-gray-400">Pilih Kegiatan untuk menampilkan Subkegiatan</span>
                        </div>

                        <select name="sub_kegiatan_id" x-model="selectedSub" :disabled="!selectedKegiatan"
                            @change="onSubChange()"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600 disabled:bg-gray-100"
                            required>
                            <option value="">— Pilih Subkegiatan —</option>

                            {{-- Fallback jika sub terpilih tidak ada di filteredSubs --}}
                            <template
                                x-if="selectedSub && !filteredSubs.some(s => String(s.id) === String(selectedSub))">
                                <option :value="String(selectedSub)" x-text="dbSubNama || '(Subkegiatan saat ini)'">
                                </option>
                            </template>

                            <template x-for="s in filteredSubs" :key="s.id">
                                <option :value="String(s.id)" x-text="s.nama"></option>
                            </template>
                        </select>

                        @error('sub_kegiatan_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Fisik (opsional) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Realisasi Fisik (%)</label>
                        <input type="number" name="realisasi_fisik" min="0" max="100" x-model="fisikHeader"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="0-100">
                        @error('realisasi_fisik')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Tanggal --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Realisasi</label>
                        <input type="date" name="tanggal_realisasi" x-model="tanggalHeader"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            required>
                        @error('tanggal_realisasi')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Lokasi --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lokasi</label>
                        <input type="text" name="lokasi" x-model="lokasiHeader"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="Masukkan lokasi kegiatan">
                        @error('lokasi')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Catatan --}}
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Catatan</label>
                        <textarea name="catatan" rows="3" x-model="catatanHeader"
                            class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                            placeholder="Catatan tambahan"></textarea>
                        @error('catatan')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- ======= TABEL RINCIAN ======= --}}
                <div class="rounded-lg border border-gray-200">
                    <div class="flex items-center justify-between p-3 sm:p-4">
                        <h3 class="text-sm font-semibold text-gray-800">Rincian Realisasi</h3>
                        <div class="flex items-center gap-3">
                            <div class="text-sm">
                                <span class="text-gray-500">Total dari rincian:</span>
                                <span class="font-semibold text-gray-900" x-text="formatRupiah(totalAnggaran)"></span>
                            </div>
                            <button type="button" @click="addRow()"
                                class="inline-flex items-center h-9 px-3 text-xs font-medium rounded-md bg-blue-600 text-white hover:bg-blue-700">
                                <i class="fas fa-plus mr-1.5"></i> Tambah Baris
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-gray-700 text-xs uppercase tracking-wide">
                                    <th class="text-left font-semibold px-3 py-2.5 w-[38%]">Rincian</th>
                                    <th class="text-left font-semibold px-3 py-2.5 w-[18%]">Realisasi Anggaran (Rp)</th>
                                    <th class="text-left font-semibold px-3 py-2.5 w-[12%]">Fisik %</th>
                                    <th class="text-left font-semibold px-3 py-2.5 w-[12%]">Volume</th>
                                    <th class="text-left font-semibold px-3 py-2.5 w-[14%]">Tanggal</th>
                                    <th class="text-left font-semibold px-3 py-2.5 w-[6%]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(row, i) in rows" :key="i">
                                    <tr class="hover:bg-gray-50/60">
                                        {{-- pilih rincian --}}
                                        <td class="px-3 py-2.5">
                                            <select :name="`rincians[${i}][rincian_kegiatan_id]`"
                                                class="w-full border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                                                required>
                                                <option value="">— Pilih Rincian —</option>
                                                <template x-for="r in filteredRincians" :key="r.id">
                                                    <option :value="String(r.id)"
                                                        :selected="String(row.rincian_kegiatan_id)===String(r.id)"
                                                        x-text="r.uraian"></option>
                                                </template>
                                            </select>
                                        </td>

                                        {{-- anggaran --}}
                                        <td class="px-3 py-2.5">
                                            <input type="number" min="0" :name="`rincians[${i}][realisasi_anggaran]`"
                                                x-model.number="row.realisasi_anggaran"
                                                class="w-full border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                                                placeholder="0" required>
                                        </td>

                                        {{-- fisik --}}
                                        <td class="px-3 py-2.5">
                                            <input type="number" min="0" max="100"
                                                :name="`rincians[${i}][realisasi_fisik]`"
                                                x-model.number="row.realisasi_fisik"
                                                class="w-full border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                                                placeholder="0-100">
                                        </td>

                                        {{-- volume --}}
                                        <td class="px-3 py-2.5">
                                            <input type="number" min="0" step="0.01"
                                                :name="`rincians[${i}][realisasi_volume]`"
                                                x-model.number="row.realisasi_volume"
                                                class="w-full border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600"
                                                placeholder="0">
                                        </td>

                                        {{-- tanggal --}}
                                        <td class="px-3 py-2.5">
                                            <input type="date" :name="`rincians[${i}][tanggal_realisasi]`"
                                                x-model="row.tanggal_realisasi"
                                                class="w-full border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600">
                                        </td>

                                        {{-- hapus baris --}}
                                        <td class="px-3 py-2.5">
                                            <button type="button" @click="removeRow(i)"
                                                class="inline-flex items-center h-8 px-2.5 text-[11px] font-medium rounded-md bg-red-600 text-white hover:bg-red-700">
                                                <i class="fas fa-trash mr-1"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- error nested --}}
                    <div class="p-3 sm:p-4">
                        @error('rincians')<p class="text-red-500 text-sm">{{ $message }}</p>@enderror
                        @error('rincians.*.rincian_kegiatan_id')<p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                        @error('rincians.*.realisasi_anggaran')<p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Dokumen tambahan --}}
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Upload Dokumen (opsional)</label>
                    <input type="file" name="dokumen[]" multiple
                        class="w-full mt-1 border-gray-300 text-sm rounded-md focus:ring-green-600 focus:border-green-600">
                    <p class="text-xs text-gray-500 mt-1">Format: PDF, JPG, PNG, DOC, DOCX. Maks 10MB per file.</p>
                    @error('dokumen.*')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100 mt-4">
                    <a href="{{ route('realisasi.index') }}"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                        <i class="fas fa-times mr-1"></i> Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                        <i class="fas fa-save mr-1"></i> Update Realisasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function realisasiEditForm(cfg) {
            const toStr = v => (v === undefined || v === null) ? '' : String(v);
            return {
                // master
                kegiatans: cfg.kegiatans || [],
                subs: cfg.subs || [],
                rinciansMaster: cfg.rinciansMaster || [],

                // label fallback
                dbKegiatanNama: cfg.dbKegiatanNama || '',
                dbSubNama:      cfg.dbSubNama || '',

                // header (old() dulu, fallback DB) sebagai STRING
                selectedKegiatan: toStr(cfg.presetKegiatan) || toStr(cfg.dbKegiatan),
                selectedSub:      toStr(cfg.presetSub)      || toStr(cfg.dbSub),
                tanggalHeader:    cfg.presetTanggal || cfg.dbTanggal || '',
                lokasiHeader:     (cfg.presetLokasi ?? cfg.dbLokasi ?? ''),
                catatanHeader:    (cfg.presetCatatan ?? cfg.dbCatatan ?? ''),
                fisikHeader:      toStr(cfg.presetFisik) || toStr(cfg.dbFisik),

                // detail rows
                rows: Array.isArray(cfg.presetRows) && cfg.presetRows.length ? cfg.presetRows : [{
                    rincian_kegiatan_id: '',
                    realisasi_anggaran: '',
                    realisasi_fisik: '',
                    realisasi_volume: '',
                    tanggal_realisasi: cfg.presetTanggal || cfg.dbTanggal || '',
                    lokasi: cfg.presetLokasi ?? cfg.dbLokasi ?? '',
                    catatan: '',
                }],

                get filteredSubs() {
                    if (!this.selectedKegiatan) return [];
                    return this.subs.filter(s => String(s.kegiatan_id) === String(this.selectedKegiatan));
                },
                get filteredRincians() {
                    if (!this.selectedSub) return [];
                    return this.rinciansMaster.filter(r => String(r.sub_kegiatan_id) === String(this.selectedSub));
                },

                onKegiatanChange() {
                    this.selectedSub = '';
                    this.rows = [{
                        rincian_kegiatan_id: '',
                        realisasi_anggaran: '',
                        realisasi_fisik: '',
                        realisasi_volume: '',
                        tanggal_realisasi: this.tanggalHeader || '',
                        lokasi: this.lokasiHeader || '',
                        catatan: '',
                    }];
                },
                onSubChange() {
                    this.rows = [{
                        rincian_kegiatan_id: '',
                        realisasi_anggaran: '',
                        realisasi_fisik: '',
                        realisasi_volume: '',
                        tanggal_realisasi: this.tanggalHeader || '',
                        lokasi: this.lokasiHeader || '',
                        catatan: '',
                    }];
                },

                addRow() {
                    this.rows.push({
                        rincian_kegiatan_id: '',
                        realisasi_anggaran: '',
                        realisasi_fisik: '',
                        realisasi_volume: '',
                        tanggal_realisasi: this.tanggalHeader || '',
                        lokasi: this.lokasiHeader || '',
                        catatan: '',
                    });
                },
                removeRow(i) {
                    this.rows.splice(i, 1);
                    if (this.rows.length === 0) this.addRow();
                },

                get totalAnggaran() {
                    return this.rows.reduce((sum, r) => sum + (parseFloat(r.realisasi_anggaran) || 0), 0);
                },
                formatRupiah(v) {
                    const n = Number(v || 0);
                    return 'Rp ' + n.toLocaleString('id-ID', {maximumFractionDigits: 0});
                },
            }
        }
    </script>
</x-app-layout>