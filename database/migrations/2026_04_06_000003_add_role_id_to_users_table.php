<?php

declare(strict_types=1);

use Core\Migration\Blueprint;
use Core\Migration\SchemaBuilder;

return new class
{
    public function up(SchemaBuilder $schema): void
    {
        if (!$schema->hasTable('users') || !$schema->hasTable('roles')) {
            return;
        }

        if ($schema->hasColumn('users', 'role_id')) {
            return;
        }

        $schema->table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable();
            $table->index('role_id');
        });
    }

    public function down(SchemaBuilder $schema): void
    {
        if (!$schema->hasTable('users') || !$schema->hasColumn('users', 'role_id')) {
            return;
        }

        $schema->table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_id_index');
            $table->dropColumn('role_id');
        });
    }
};
