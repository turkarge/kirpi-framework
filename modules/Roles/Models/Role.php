<?php

declare(strict_types=1);

namespace Modules\Roles\Models;

use Core\Model\Model;

final class Role extends Model
{
    protected string $table = 'roles';

    protected array $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'is_system',
        'user_count',
        'sort_order',
    ];

    protected array $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'user_count' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

