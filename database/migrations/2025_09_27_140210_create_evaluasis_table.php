<?php
// database/migrations/2024_01_01_000005_create_evaluasis_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('evaluasis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kegiatan_id');
            $table->unsignedBigInteger('evaluator_id'); // Admin/Pimpinan
            $table->enum('status_evaluasi', ['on_track', 'terlambat', 'tidak_sesuai']);
            $table->text('catatan_evaluasi');
            $table->text('rekomendasi')->nullable();
            $table->date('tanggal_evaluasi');
            $table->timestamps();
            
            $table->foreign('kegiatan_id')->references('id')->on('kegiatans');
            $table->foreign('evaluator_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluasis');
    }
};