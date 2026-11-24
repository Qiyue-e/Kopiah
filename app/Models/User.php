<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = ['nama', 'username', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /** Dashboard tujuan sesuai role. */
    public function homeUrl(): string
    {
        return $this->isOwner() ? route('admin.dashboard') : route('kasir.dashboard');
    }

    public function scopeKasir(Builder $query): Builder
    {
        return $query->where('role', 'kasir');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'kasir_id');
    }

    public function cashSessions(): HasMany
    {
        return $this->hasMany(CashSession::class);
    }
}
