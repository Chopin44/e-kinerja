<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rincian_kegiatans', function (Blueprint $table) {
            // enum sesuai kebutuhanmu; bisa juga string kalau mau fleksibel
            $table->enum('kategori', ['pengadaan_langsung','swakelola','pokir'])
                  ->nullable()
                  ->after('anggaran');
        });
    }

    public function down(): void
    {
        Schema::table('rincian_kegiatans', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};
