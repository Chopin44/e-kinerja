<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('realisasi_rincians', function (Blueprint $table) {
            // Tambah kolom relasi ke header realisasi
            if (!Schema::hasColumn('realisasi_rincians', 'realisasi_id')) {
                $table->foreignId('realisasi_id')
                      ->after('id')
                      ->nullable() // buat nullable dulu biar aman kalau ada data lama
                      ->constrained('realisasis')
                      ->cascadeOnDelete();
            }

            // (Opsional) kalau kamu mau simpan % fisik di level rincian
            if (!Schema::hasColumn('realisasi_rincians', 'realisasi_fisik')) {
                $table->decimal('realisasi_fisik', 5, 2)->nullable()->after('realisasi_anggaran');
            }

            // Tambah index kecil untuk performa (opsional tapi bagus)
            $table->index(['realisasi_id']);
            $table->index(['rincian_kegiatan_id']);
        });
    }

    public function down(): void
    {
        Schema::table('realisasi_rincians', function (Blueprint $table) {
            if (Schema::hasColumn('realisasi_rincians', 'realisasi_fisik')) {
                $table->dropColumn('realisasi_fisik');
            }
            if (Schema::hasColumn('realisasi_rincians', 'realisasi_id')) {
                $table->dropForeign(['realisasi_id']);
                $table->dropColumn('realisasi_id');
            }
        });
    }
};
