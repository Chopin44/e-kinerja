<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'nip', 'phone', 'bidang_id', 'role', 'password', 'is_active', 'last_login_at'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    public function kegiatans()
    {
        return $this->hasMany(Kegiatan::class);
    }

    public function realisasis()
    {
        return $this->hasMany(Realisasi::class);
    }

    public function evaluasis()
    {
        return $this->hasMany(Evaluasi::class, 'evaluator_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    // Accessors
    public function getIsAdminAttribute()
    {
        return $this->role === 'admin';
    }
}