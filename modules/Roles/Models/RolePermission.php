<?php

declare(strict_types=1);

namespace Modules\Roles\Models;

use Core\Model\Model;

final class RolePermission extends Model
{
    protected string $table = 'role_permissions';

    protected array $fillable = [
        'role_id',
        'permission_key',
        'is_allowed',
    ];

    protected array $casts = [
        'role_id' => 'integer',
        'is_allowed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

