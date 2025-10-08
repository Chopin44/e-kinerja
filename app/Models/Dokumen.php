<?php
// app/Models/Dokumen.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Dokumen extends Model
{
    use HasFactory;

    protected $fillable = [
        'realisasi_id', 'nama_file', 'nama_asli', 'path', 'mime_type', 'size', 'jenis'
    ];

    // Relationships
    public function realisasi()
    {
        return $this->belongsTo(Realisasi::class);
    }

    // Accessors
    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }

    public function getSizeHumanAttribute()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}