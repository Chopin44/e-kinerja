<?php
// database/migrations/2024_01_01_000003_create_realisasis_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('realisasis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kegiatan_id');
            $table->unsignedBigInteger('user_id'); // Yang input
            $table->decimal('realisasi_fisik', 5, 2); // Persentase
            $table->decimal('realisasi_anggaran', 15, 2); // Rupiah
            $table->date('tanggal_realisasi');
            $table->string('lokasi')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->timestamps();
            
            $table->foreign('kegiatan_id')->references('id')->on('kegiatans');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('realisasis');
    }
};