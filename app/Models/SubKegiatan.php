<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubKegiatan extends Model
{
    use HasFactory;

    // Laravel akan otomatis pakai tabel "sub_kegiatans"
    // kalau nama tabelmu beda, tambahkan: protected $table = 'nama_tabel';

    protected $fillable = [
        'kegiatan_id',
        'user_id',
        'nama',
        'deskripsi',
        'target_anggaran',
        'target_fisik',
        'realisasi_anggaran',
        'periode_type',
        'tahun',
    ];

    /**
     * Catatan: cast 'decimal:2' di Laravel mengembalikan STRING (untuk akurasi).
     * Kalau kamu butuh numerik untuk hitung cepat, gunakan accessor di bawah.
     */
    protected $casts = [
        'target_anggaran'    => 'decimal:2',
        'realisasi_anggaran' => 'decimal:2',
        'tahun'              => 'integer',
    ];

    /** =======================
     * Relations
     * ======================= */
    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rincianKegiatans()
    {
        return $this->hasMany(RincianKegiatan::class);
    }

    public function realisasis()
    {
        return $this->hasMany(Realisasi::class, 'sub_kegiatan_id');
    }


    /** =======================
     * Scopes (opsional, tapi enak dipakai)
     * ======================= */
    public function scopeByTahun($query, ?int $tahun)
    {
        return $tahun ? $query->where('tahun', $tahun) : $query;
    }

    public function scopeByPeriode($query, ?string $periodeType)
    {
        return $periodeType ? $query->where('periode_type', $periodeType) : $query;
    }

    public function scopeByUser($query, ?int $userId)
    {
        return $userId ? $query->where('user_id', $userId) : $query;
    }

    /** =======================
     * Accessors helper (numerik)
     * ======================= */
    public function getTargetAnggaranNumAttribute(): float
    {
        return (float) ($this->target_anggaran ?? 0);
    }

    public function getRealisasiAnggaranNumAttribute(): float
    {
        return (float) ($this->realisasi_anggaran ?? 0);
    }

    public function getSisaAnggaranAttribute(): float
    {
        return max(0, $this->target_anggaran_num - $this->realisasi_anggaran_num);
    }

    public function getPersenRealisasiAttribute(): float
    {
        $target = $this->target_anggaran_num;
        return $target > 0 ? round(($this->realisasi_anggaran_num / $target) * 100, 2) : 0.0;
    }
}
