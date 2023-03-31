<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $with = ['roles'];

    protected $hidden = [
        'password',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $roleName)
    {
        return $this->roles->contains(fn ($role) => $role->name == $roleName);
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        $query->when(
            $filters['role'] ?? null,
            fn (Builder $query, $role) => $query->whereRelation('roles', 'name', $role)
        );
    }
}
