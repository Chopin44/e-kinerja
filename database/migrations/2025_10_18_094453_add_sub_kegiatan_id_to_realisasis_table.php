<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('realisasis', function (Blueprint $table) {
            // kalau sudah ada, jangan ditambah lagi
            if (!Schema::hasColumn('realisasis', 'sub_kegiatan_id')) {
                // nullable dulu supaya data lama tidak error.
                // Setelah semua data lama diisi, boleh diubah ke NOT NULL.
                $table->foreignId('sub_kegiatan_id')
                      ->nullable()
                      ->after('kegiatan_id')
                      ->constrained('sub_kegiatans')
                      ->nullOnDelete(); // kalau subkegiatan dihapus, set null
            }
        });
    }

    public function down(): void
    {
        Schema::table('realisasis', function (Blueprint $table) {
            if (Schema::hasColumn('realisasis', 'sub_kegiatan_id')) {
                $table->dropForeign(['sub_kegiatan_id']);
                $table->dropColumn('sub_kegiatan_id');
            }
        });
    }
};
