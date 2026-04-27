<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relasi: user punya banyak transaksi
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Relasi: user punya banyak activity logs
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Cek role (helper method)
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isManajer()
    {
        return $this->role === 'manajer';
    }

    public function isKasir()
    {
        return $this->role === 'kasir';
    }
}
