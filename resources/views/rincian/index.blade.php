<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-6 px-4">
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm border p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">Rincian Subkegiatan</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $subkegiatan->nama }} • {{ $subkegiatan->kegiatan->bidang->nama }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('subkegiatan.edit', $subkegiatan) }}"
                        class="inline-flex items-center h-9 px-3 text-xs font-medium rounded-md bg-green-600 text-white hover:bg-green-700">
                        <i class="fas fa-pen mr-1.5"></i> Edit Subkegiatan
                    </a>

                    <a href="{{ route('kegiatan.index') }}"
                        class="inline-flex items-center h-9 px-3 text-xs font-medium rounded-md bg-gray-100 text-gray-800 hover:bg-gray-200">
                        <i class="fas fa-arrow-left mr-1.5"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        {{-- Card table --}}
        <div class="bg-white rounded-lg shadow-sm border p-5 sm:p-6">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h2 class="text-base font-semibold text-gray-800">Daftar Rincian</h2>

                {{-- CREATE (nested) --}}
                <a href="{{ route('subkegiatan.rincian.create', $subkegiatan) }}"
                    class="inline-flex items-center h-9 px-3 text-xs font-medium rounded-md bg-blue-600 text-white hover:bg-blue-700">
                    <i class="fas fa-plus mr-1.5"></i> Tambah Rincian
                </a>
            </div>

            {{-- Pesan sukses --}}
            @if(session('success'))
            <div class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded p-2.5">
                {{ session('success') }}
            </div>
            @endif

            {{-- Tabel rincian --}}
            @if($rincians->isEmpty())
            <p class="text-sm text-gray-500">Belum ada rincian.</p>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-gray-700 text-xs uppercase tracking-wide">
                            <th class="text-left font-semibold px-3 py-2.5">Uraian</th>
                            <th class="text-left font-semibold px-3 py-2.5">Kategori</th>
                            <th class="text-right font-semibold px-3 py-2.5">Anggaran (Rp)</th>
                            <th class="text-right font-semibold px-3 py-2.5">Fisik (%)</th>
                            <th class="text-left font-semibold px-3 py-2.5 w-40">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @foreach($rincians as $r)
                        <tr class="hover:bg-gray-50/60">
                            {{-- Uraian --}}
                            <td class="px-3 py-2.5 text-gray-900">
                                {{ $r->uraian }}
                            </td>

                            {{-- Kategori --}}
                            <td class="px-3 py-2.5">
                                <span
                                    class="inline-flex items-center rounded-md bg-gray-100 text-gray-700 px-2 py-0.5 text-[11px] font-medium">
                                    {{ $r->kategori ? ucwords(str_replace('_',' ',$r->kategori)) : '-' }}
                                </span>
                            </td>

                            {{-- Anggaran --}}
                            <td class="px-3 py-2.5 text-left">
                                Rp {{ number_format($r->anggaran ?? 0, 0, ',', '.') }}
                            </td>

                            {{-- Target Fisik --}}
                            <td class="px-3 py-2.5 text-center">
                                {{ number_format($r->target_fisik ?? 0, 1) }}%
                            </td>

                            {{-- Aksi --}}
                            <td class="px-3 py-2.5">
                                <div class="flex items-center gap-1.5 justify-start">
                                    {{-- EDIT (shallow) --}}
                                    <a href="{{ route('rincian.edit', $r) }}"
                                        class="inline-flex items-center h-8 px-2.5 text-[11px] font-medium rounded-md bg-blue-600 text-white hover:bg-blue-700">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>

                                    {{-- DELETE (shallow) --}}
                                    <form action="{{ route('rincian.destroy', $r) }}" method="POST"
                                        onsubmit="return confirm('Hapus rincian ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            class="inline-flex items-center h-8 px-2.5 text-[11px] font-medium rounded-md bg-red-600 text-white hover:bg-red-700">
                                            <i class="fas fa-trash mr-1"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>