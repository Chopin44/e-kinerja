<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rincian_kegiatans', function (Blueprint $table) {
            $table->id();

            // Parent: sub_kegiatans.id
            $table->foreignId('sub_kegiatan_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Kolom utama sesuai model
            $table->string('uraian');
            $table->decimal('anggaran', 15, 2)->nullable();
            $table->decimal('realisasi', 15, 2)->nullable();
            $table->string('satuan')->nullable();   // contoh: paket, unit, meter
            $table->integer('volume')->nullable();  // jumlah volume

            $table->timestamps();

            // Index yang sering dipakai untuk query rekap
            $table->index(['sub_kegiatan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rincian_kegiatans');
    }
};
