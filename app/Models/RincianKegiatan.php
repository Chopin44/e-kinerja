<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RincianKegiatan extends Model
{
    use HasFactory;

    // Tabel default: "rincian_kegiatans"
    protected $fillable = [
    'sub_kegiatan_id','uraian','anggaran','realisasi','kategori'
    ];


    protected $casts = [
        'anggaran' => 'decimal:2',
        'realisasi' => 'decimal:2',
    ];

    /** =======================
     * Relations
     * ======================= */
    public function subKegiatan()
    {
        return $this->belongsTo(SubKegiatan::class);
    }

    public function realisasiRincians()
    {
        return $this->hasMany(\App\Models\RealisasiRincian::class, 'rincian_kegiatan_id');
    }


    /** =======================
     * Accessors helper (numerik)
     * ======================= */
    public function getAnggaranNumAttribute(): float
    {
        return (float) ($this->anggaran ?? 0);
    }

    public function getRealisasiNumAttribute(): float
    {
        return (float) ($this->realisasi ?? 0);
    }

    public function getSisaAttribute(): float
    {
        return max(0, $this->anggaran_num - $this->realisasi_num);
    }
}
