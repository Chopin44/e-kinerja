<?php
// database/migrations/2024_01_01_000004_create_dokumens_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dokumens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('realisasi_id');
            $table->string('nama_file');
            $table->string('nama_asli');
            $table->string('path');
            $table->string('mime_type');
            $table->integer('size'); // bytes
            $table->enum('jenis', ['foto', 'laporan', 'kwitansi', 'lainnya']);
            $table->timestamps();
            
            $table->foreign('realisasi_id')->references('id')->on('realisasis');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dokumens');
    }
};