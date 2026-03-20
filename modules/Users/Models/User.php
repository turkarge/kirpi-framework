<?php

declare(strict_types=1);

namespace Modules\Users\Models;

use Core\Model\Model;

class User extends Model
{
    protected string $table = 'users';

    protected array $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'locale',
        'provider',
        'provider_id',
        'is_active',
    ];

    protected array $hidden = [
        'password',
        'remember_token',
    ];

    protected array $casts = [
        'is_active'         => 'boolean',
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
    ];

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = password_hash(
            $value,
            PASSWORD_ARGON2ID
        );
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->attributes['avatar']
            ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->attributes['name'] ?? 'User');
    }

    public function scopeActive(\Core\Database\QueryBuilder $query): \Core\Database\QueryBuilder
    {
        return $query->where('is_active', 1);
    }

    public function scopeVerified(\Core\Database\QueryBuilder $query): \Core\Database\QueryBuilder
    {
        return $query->whereNotNull('email_verified_at');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('softDelete', function (\Core\Database\QueryBuilder $query) {
            $query->whereNull('deleted_at');
        });
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->attributes['password'] ?? '');
    }
}