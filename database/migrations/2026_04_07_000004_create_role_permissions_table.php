<?php

declare(strict_types=1);

use Core\Migration\Blueprint;
use Core\Migration\SchemaBuilder;

return new class
{
    public function up(SchemaBuilder $schema): void
    {
        if ($schema->hasTable('role_permissions')) {
            return;
        }

        $schema->create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->string('permission_key', 120);
            $table->boolean('is_allowed')->default(1);
            $table->timestamps();

            $table->unique(['role_id', 'permission_key']);
            $table->index('role_id');
            $table->index('permission_key');
        });
    }

    public function down(SchemaBuilder $schema): void
    {
        $schema->dropIfExists('role_permissions');
    }
};
