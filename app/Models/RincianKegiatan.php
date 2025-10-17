<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RincianKegiatan extends Model
{
    use HasFactory;

    // Tabel default: "rincian_kegiatans"
    protected $fillable = [
    'sub_kegiatan_id','uraian','anggaran','realisasi','satuan','volume','kategori'
    ];


    protected $casts = [
        'anggaran' => 'decimal:2',
        'realisasi' => 'decimal:2',
        'volume' => 'integer',
    ];

    /** =======================
     * Relations
     * ======================= */
    public function subKegiatan()
    {
        return $this->belongsTo(SubKegiatan::class);
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
