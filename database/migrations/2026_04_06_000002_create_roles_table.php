<?php

declare(strict_types=1);

use Core\Migration\Blueprint;
use Core\Migration\SchemaBuilder;

return new class
{
    public function up(SchemaBuilder $schema): void
    {
        $schema->create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(1);
            $table->boolean('is_system')->default(0);
            $table->unsignedInteger('user_count')->default(0);
            $table->unsignedInteger('sort_order')->default(100);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(SchemaBuilder $schema): void
    {
        $schema->dropIfExists('roles');
    }
};

