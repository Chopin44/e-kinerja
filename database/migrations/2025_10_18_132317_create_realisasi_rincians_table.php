<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('realisasi_rincians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rincian_kegiatan_id')->constrained('rincian_kegiatans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // angka realisasi
            $table->decimal('realisasi_anggaran', 20, 2)->default(0);
            $table->decimal('realisasi_volume', 12, 2)->nullable(); // opsional, kalau mau catat volume
            $table->date('tanggal_realisasi');
            $table->string('lokasi')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realisasi_rincians');
    }
};
