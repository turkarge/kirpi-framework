<?php

declare(strict_types=1);

use Core\Migration\Blueprint;
use Core\Migration\SchemaBuilder;

return new class
{
    public function up(SchemaBuilder $schema): void
    {
        if (!$schema->hasTable('users') || $schema->hasColumn('users', 'lock_pin_hash')) {
            return;
        }

        $schema->table('users', function (Blueprint $table) {
            $table->string('lock_pin_hash', 255)->nullable();
        });
    }

    public function down(SchemaBuilder $schema): void
    {
        if (!$schema->hasTable('users') || !$schema->hasColumn('users', 'lock_pin_hash')) {
            return;
        }

        $schema->table('users', function (Blueprint $table) {
            $table->dropColumn('lock_pin_hash');
        });
    }
};
