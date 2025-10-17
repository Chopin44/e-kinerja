<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained()->onDelete('cascade'); // parent → kegiatans.id
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // staf admin → users.id
            $table->string('kode_subkegiatan')->nullable();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->decimal('target_anggaran', 15, 2)->nullable();
            $table->decimal('realisasi_anggaran', 15, 2)->nullable();
            $table->string('periode_type')->nullable(); // triwulan_1..4
            $table->integer('tahun')->nullable();
            $table->timestamps();

            $table->index(['kegiatan_id', 'tahun', 'periode_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_kegiatans');
    }
};
