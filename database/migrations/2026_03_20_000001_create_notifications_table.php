<?php

declare(strict_types=1);

use Core\Migration\Blueprint;
use Core\Migration\SchemaBuilder;

return new class
{
    public function up(SchemaBuilder $schema): void
    {
        $schema->create('notifications', function (Blueprint $table) {
            $table->string('id', 32);
            $table->string('type');
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->primary(['id']);
            $table->index(['notifiable_type', 'notifiable_id']);
        });
    }

    public function down(SchemaBuilder $schema): void
    {
        $schema->dropIfExists('notifications');
    }
};