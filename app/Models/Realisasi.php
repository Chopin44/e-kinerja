<?php
// app/Models/Realisasi.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Realisasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'kegiatan_id', 'user_id', 'realisasi_fisik', 'realisasi_anggaran',
        'tanggal_realisasi', 'lokasi', 'catatan', 'status'
    ];

    protected $casts = [
        'realisasi_fisik' => 'decimal:2',
        'realisasi_anggaran' => 'decimal:2',
        'tanggal_realisasi' => 'date',
    ];

    // Relationships
    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dokumens()
    {
        return $this->hasMany(Dokumen::class);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByPeriode($query, $start, $end)
    {
        return $query->whereBetween('tanggal_realisasi', [$start, $end]);
    }
}