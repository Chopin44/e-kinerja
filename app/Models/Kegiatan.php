<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama','deskripsi','bidang_id','user_id', 'periode_type',
        'tanggal_mulai','tanggal_selesai',
        'tahun','status',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    // ===== Relationships
    public function bidang(){ return $this->belongsTo(Bidang::class); }
    public function user(){ return $this->belongsTo(User::class); }
    public function realisasis(){ return $this->hasMany(Realisasi::class); }
    public function evaluasis(){ return $this->hasMany(Evaluasi::class); }

    // ➕ baru: anak sub-kegiatan
    public function subKegiatans()
    {
        return $this->hasMany(SubKegiatan::class);
    }

    // ===== Scopes
    public function scopeAktif($q){ return $q->where('status','aktif'); }
    public function scopeByBidang($q,$bidangId){ return $q->where('bidang_id',$bidangId); }
    public function scopeByTahun($q,$tahun){ return $q->where('tahun',$tahun); }

    // ===== Accessors lama (biarkan)
    public function getPeriodeAttribute(): ?string
    { return $this->attributes['periode_type'] ?? null; }

    public function getLatestRealisasiAttribute()
    {
        return $this->realisasis()->orderByDesc('tanggal_realisasi')->orderByDesc('id')->first();
    }

    public function getCurrentProgressAttribute(): float
    {
        $latest = $this->latest_realisasi;
        return $latest ? (float) $latest->realisasi_fisik : 0.0;
    }

    public function getCurrentBudgetRealizationAttribute(): float
    {
        return (float) $this->realisasis()->sum('realisasi_anggaran');
    }

    public function getStatusEvaluasiAttribute(): ?string
    {
        $latestEval = $this->evaluasis()->orderByDesc('tanggal_evaluasi')->orderByDesc('id')->first();
        if ($latestEval) return $latestEval->status_evaluasi;

        $current = (float) $this->current_progress;
        $target  = (float) ($this->target_fisik ?? 0);

        if ($target <= 0) return 'tidak_sesuai';
        if ($current >= $target * 0.9) return 'on_track';
        if ($current >= $target * 0.7) return 'terlambat';
        return 'tidak_sesuai';
    }
}
