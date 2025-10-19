<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RealisasiRincian extends Model
{
    protected $table = 'realisasi_rincians'; // pastikan sesuai migrasi

    protected $fillable = [
        'realisasi_id',
        'rincian_kegiatan_id',
        'user_id',
        'realisasi_anggaran',
        'realisasi_fisik',
        'tanggal_realisasi',
        'lokasi',
        'catatan',
    ];

    protected $casts = [
        'tanggal_realisasi'  => 'date',
        'realisasi_anggaran' => 'decimal:2',
        'realisasi_fisik'    => 'decimal:2',
    ];

    public function realisasi()
    {
        return $this->belongsTo(Realisasi::class);
    }

    public function rincianKegiatan()
    {
        return $this->belongsTo(RincianKegiatan::class, 'rincian_kegiatan_id');
    }
}
