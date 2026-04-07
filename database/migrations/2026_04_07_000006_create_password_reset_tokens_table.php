<?php

declare(strict_types=1);

use Core\Migration\Blueprint;
use Core\Migration\SchemaBuilder;

return new class
{
    public function up(SchemaBuilder $schema): void
    {
        if ($schema->hasTable('password_reset_tokens')) {
            return;
        }

        $schema->create('password_reset_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('expires_at');
        });
    }

    public function down(SchemaBuilder $schema): void
    {
        $schema->dropIfExists('password_reset_tokens');
    }
};

