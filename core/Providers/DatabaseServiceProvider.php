<?php

declare(strict_types=1);

namespace Core\Providers;

use Core\Database\DatabaseManager;
use Core\Migration\MigrationRepository;
use Core\Migration\Migrator;
use Core\Migration\SchemaBuilder;
use Core\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $config = $this->app->make('config')->load('database');

        $db = new DatabaseManager($config);
        $this->app->instance('db', $db);
        $this->app->instance(DatabaseManager::class, $db);

        $schema = new SchemaBuilder($db);
        $repository = new MigrationRepository($db);
        $migrator = new Migrator(
            schema: $schema,
            repository: $repository,
            path: base_path('database/migrations'),
        );

        $this->app->instance(SchemaBuilder::class, $schema);
        $this->app->instance(MigrationRepository::class, $repository);
        $this->app->instance(Migrator::class, $migrator);
    }
}