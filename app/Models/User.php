<?php

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
        'name',
        'username',
        'nip',
        'bidang_id',
        // 'role',  // ⛔️ HAPUS: kita tidak menyimpan role di kolom users
        'password',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'is_active'         => 'boolean',
    ];

    /** =======================
     *  Relationships
     *  ======================= */
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

    /** =======================
     *  Scopes
     *  ======================= */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by role via Spatie (bukan kolom).
     * Contoh pakai: User::byRole('admin')->get()
     */
    public function scopeByRole($query, string $roleName)
    {
        // Spatie menyediakan scope "role" langsung di query builder juga,
        // tapi kita bungkus agar konsisten dengan pemakaian sebelumnya.
        return $query->role($roleName);
    }

    /** =======================
     *  Accessors
     *  ======================= */
    public function getIsAdminAttribute(): bool
    {
        return $this->hasRole('admin');
    }
}
