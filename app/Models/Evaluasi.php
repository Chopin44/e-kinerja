<?php
// app/Models/Evaluasi.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'kegiatan_id', 'evaluator_id', 'status_evaluasi', 'catatan_evaluasi',
        'rekomendasi', 'tanggal_evaluasi'
    ];

    protected $casts = [
        'tanggal_evaluasi' => 'date',
    ];

    // Relationships
    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}