<x-app-layout>
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Kegiatan</h1>
                    <p class="text-gray-600 mt-1">Perbarui data kegiatan untuk monitoring</p>
                </div>
                <a href="{{ route('monitoring.index') }}" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('monitoring.update', $kegiatan) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kegiatan</label>
                        <input type="text" name="nama" class="form-input" value="{{ old('nama', $kegiatan->nama) }}"
                            required>
                        @error('nama')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bidang</label>
                        <select name="bidang_id" class="form-select" required>
                            @foreach($bidangs as $b)
                            <option value="{{ $b->id }}" {{ (old('bidang_id',$kegiatan->bidang_id)==$b->id) ?
                                'selected':'' }}>
                                {{ $b->nama }}
                            </option>
                            @endforeach
                        </select>
                        @error('bidang_id')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                        <select name="periode" class="form-select">
                            <option value="">-</option>
                            @foreach(['Q1','Q2','Q3','Q4'] as $q)
                            <option value="{{ $q }}" {{ old('periode',$kegiatan->periode)===$q ? 'selected':'' }}>{{ $q
                                }}</option>
                            @endforeach
                        </select>
                        @error('periode')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="form-select">
                            @foreach(['on_track'=>'On
                            Track','late'=>'Terlambat','problem'=>'Bermasalah','aktif'=>'Aktif','selesai'=>'Selesai'] as
                            $val=>$label)
                            <option value="{{ $val }}" {{ old('status',$kegiatan->status)===$val ? 'selected':'' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                        @error('status')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Target Fisik (%)</label>
                        <input type="number" name="target_fisik" class="form-input" min="0" max="100"
                            value="{{ old('target_fisik',$kegiatan->target_fisik) }}">
                        @error('target_fisik')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Target Anggaran (Rp)</label>
                        <input type="number" name="target_anggaran" class="form-input" min="0"
                            value="{{ old('target_anggaran',$kegiatan->target_anggaran) }}">
                        @error('target_anggaran')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-input"
                            value="{{ old('tanggal_mulai', optional($kegiatan->tanggal_mulai)->format('Y-m-d')) }}">
                        @error('tanggal_mulai')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-input"
                            value="{{ old('tanggal_selesai', optional($kegiatan->tanggal_selesai)->format('Y-m-d')) }}">
                        @error('tanggal_selesai')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea name="deskripsi" rows="4"
                            class="form-input">{{ old('deskripsi',$kegiatan->deskripsi) }}</textarea>
                        @error('deskripsi')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t">
                    <a href="{{ route('monitoring.index') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Batal</a>
                    <button class="btn-primary"><i class="fas fa-save mr-2"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>


</x-app-layout>