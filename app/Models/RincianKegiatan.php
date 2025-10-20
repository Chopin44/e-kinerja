<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RincianKegiatan extends Model
{
    use HasFactory;

    protected $table = 'rincian_kegiatans';

    protected $fillable = [
        'sub_kegiatan_id',
        'uraian',
        'kategori',
        'anggaran',
        'realisasi',
        'target_fisik',
    ];

    protected $casts = [
        'anggaran' => 'decimal:2',
        'realisasi' => 'decimal:2',
        'target_fisik' => 'decimal:2',
    ];

    /* ==========================
     | 🔗 RELATIONSHIPS
     ========================== */

    public function subKegiatan()
    {
        return $this->belongsTo(SubKegiatan::class);
    }

    public function realisasiRincians()
    {
        return $this->hasMany(RealisasiRincian::class, 'rincian_kegiatan_id');
    }

    /* ==========================
     | 📊 ACCESSORS & HELPERS
     ========================== */

    public function getAnggaranNumAttribute(): float
    {
        return (float) ($this->anggaran ?? 0);
    }

    public function getRealisasiNumAttribute(): float
    {
        return (float) ($this->realisasi ?? 0);
    }

    public function getTargetFisikNumAttribute(): float
    {
        return (float) ($this->target_fisik ?? 0);
    }

    public function getSisaAttribute(): float
    {
        return max(0, $this->anggaran_num - $this->realisasi_num);
    }

    public function getPersentaseRealisasiAttribute(): float
    {
        $anggaran = $this->anggaran_num;
        if ($anggaran <= 0) return 0;
        return round(($this->realisasi_num / $anggaran) * 100, 2);
    }

    public function getProgressFisikAttribute(): float
    {
        // Jika target_fisik = 0, maka progress juga 0
        return $this->target_fisik_num > 0
            ? round(min(100, $this->target_fisik_num), 2)
            : 0;
    }
}
