<?php
// database/migrations/2024_01_01_000002_create_kegiatans_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('bidang_id');
            $table->unsignedBigInteger('user_id'); // Penanggung jawab
            $table->enum('kategori', ['belanja_langsung', 'belanja_operasional', 'program_prioritas', 'kegiatan_rutin']);
            $table->enum('periode_type', ['tahunan', 'bulanan', 'triwulan']);
            $table->decimal('target_fisik', 5, 2); // Persentase
            $table->decimal('target_anggaran', 15, 2); // Rupiah
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->year('tahun');
            $table->enum('status', ['draft', 'aktif', 'selesai', 'dibatalkan'])->default('draft');
            $table->timestamps();
            
            $table->foreign('bidang_id')->references('id')->on('bidangs');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kegiatans');
    }
};