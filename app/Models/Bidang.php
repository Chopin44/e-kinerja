<?php
// app/Models/Bidang.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama', 'kode', 'deskripsi', 'kepala_bidang', 'icon', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function kegiatans()
    {
        return $this->hasMany(Kegiatan::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessors
    public function getTotalKegiatanAttribute()
    {
        return $this->kegiatans()->count();
    }

    public function getAvgProgressAttribute()
    {
        return $this->kegiatans()->with('realisasis')->get()->map(function ($kegiatan) {
            return $kegiatan->realisasis->avg('realisasi_fisik') ?? 0;
        })->avg();
    }
}