<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'deskripsi',
        'bidang_id',
        'user_id',
        'kategori',
        'periode_type',        // Q1..Q4 (pakai ini di DB kamu)
        'target_fisik',
        'target_anggaran',
        'tanggal_mulai',
        'tanggal_selesai',
        'tahun',
        'status',              // on_track/late/problem/aktif/selesai (opsional)
    ];

    protected $casts = [
        'target_fisik'      => 'decimal:2',
        'target_anggaran'   => 'decimal:2',
        'tanggal_mulai'     => 'date',
        'tanggal_selesai'   => 'date',
    ];

    /* =======================
     * Relationships
     * ======================= */
    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function realisasis()
    {
        return $this->hasMany(Realisasi::class);
    }

    public function evaluasis()
    {
        return $this->hasMany(Evaluasi::class);
    }

    /* =======================
     * Scopes
     * ======================= */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeByBidang($query, $bidangId)
    {
        return $query->where('bidang_id', $bidangId);
    }

    public function scopeByTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    /* =======================
     * Accessors & Helpers untuk Monitoring
     * ======================= */

    /**
     * Bridge: akses "periode" agar tetap bekerja di view/controller
     * yang mengakses $kegiatan->periode, meskipun field DB kamu bernama "periode_type".
     */
    public function getPeriodeAttribute(): ?string
    {
        return $this->attributes['periode_type'] ?? null;
    }

    /**
     * Realisasi terakhir berdasarkan tanggal_realisasi (paling akurat),
     * kalau sama, ambil id terbesar.
     */
    public function getLatestRealisasiAttribute()
    {
        return $this->realisasis()
            ->orderByDesc('tanggal_realisasi')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Progress fisik saat ini = progress pada realisasi terakhir.
     */
    public function getCurrentProgressAttribute(): float
    {
        $latest = $this->latest_realisasi;
        return $latest ? (float) $latest->realisasi_fisik : 0.0;
    }

    /**
     * Realisasi anggaran saat ini = JUMLAH seluruh realisasi anggaran (akumulasi),
     * supaya persentase anggaran di monitoring akurat.
     */
    public function getCurrentBudgetRealizationAttribute(): float
    {
        return (float) $this->realisasis()->sum('realisasi_anggaran');
    }

    /**
     * Status evaluasi:
     * - Utamakan dari Evaluasi terbaru (jika ada).
     * - Jika belum ada evaluasi, fallback ke heuristik progress vs target_fisik.
     */
    public function getStatusEvaluasiAttribute(): ?string
    {
        $latestEval = $this->evaluasis()
            ->orderByDesc('tanggal_evaluasi')
            ->orderByDesc('id')
            ->first();

        if ($latestEval) {
            return $latestEval->status_evaluasi; // 'on_track' | 'terlambat' | 'tidak_sesuai'
        }

        // Fallback heuristik:
        $current = (float) $this->current_progress;
        $target  = (float) ($this->target_fisik ?? 0);

        if ($target <= 0) {
            // Tanpa target, anggap "tidak_sesuai" agar mudah dideteksi (silakan ubah sesuai kebijakan)
            return 'tidak_sesuai';
        }

        if ($current >= $target * 0.9) {
            return 'on_track';
        } elseif ($current >= $target * 0.7) {
            return 'terlambat';
        } else {
            return 'tidak_sesuai';
        }
    }
}
