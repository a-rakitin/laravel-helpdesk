<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => \App\Enums\UserRole::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === \App\Enums\UserRole::ADMIN;
    }

    public function isAgent(): bool
    {
        return $this->role === \App\Enums\UserRole::AGENT;
    }

    public function isCustomer(): bool
    {
        return $this->role === \App\Enums\UserRole::CUSTOMER;
    }
}
