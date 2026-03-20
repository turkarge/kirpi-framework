<?php

declare(strict_types=1);

use Core\Container\Container;
use Core\Config\EnvLoader;
use Core\Config\Repository;
use Core\Logging\Logger;
use Core\Exception\Handler;
use Core\Database\DatabaseManager;
use Core\Migration\MigrationRepository;
use Core\Migration\Migrator;
use Core\Migration\SchemaBuilder;
use Core\Routing\Router;
use Core\Auth\AuthManager;

// Env yükle
EnvLoader::load(BASE_PATH);

// Container
$app = Container::getInstance();

// Path binding'leri
$app->instance('path.base',    BASE_PATH);
$app->instance('path.config',  BASE_PATH . '/config');
$app->instance('path.storage', BASE_PATH . '/storage');
$app->instance('path.public',  BASE_PATH . '/public');
$app->instance('path.modules', BASE_PATH . '/modules');

// Config
$config = new Repository(BASE_PATH . '/config');
$app->instance('config', $config);
$app->instance(Repository::class, $config);

// Logger
$logger = new Logger(storage_path('logs'));
$app->instance('logger', $logger);
$app->instance(Logger::class, $logger);

// Exception Handler
$handler = new Handler(
    logger: $logger,
    debug:  (bool) env('APP_DEBUG', false),
);
$app->instance(Handler::class, $handler);
$handler->register();

// Database
$dbConfig = $config->load('database');
$db       = new DatabaseManager($dbConfig);
$app->instance('db', $db);
$app->instance(DatabaseManager::class, $db);

// Migration
$schema     = new SchemaBuilder($db);
$repository = new MigrationRepository($db);
$migrator   = new Migrator(
    schema:     $schema,
    repository: $repository,
    path:       BASE_PATH . '/database/migrations',
);
$app->instance(SchemaBuilder::class,       $schema);
$app->instance(MigrationRepository::class, $repository);
$app->instance(Migrator::class,            $migrator);

// Auth
$auth = new AuthManager($config->load('auth'), $db);
$app->instance('auth', $auth);
$app->instance(AuthManager::class, $auth);

// Event
$dispatcher = new \Core\Event\EventDispatcher();
$app->instance('events', $dispatcher);
$app->instance(\Core\Event\EventDispatcher::class, $dispatcher);

// Mail
$mail = new \Core\Mail\MailManager($config->load('mail'));
$app->instance('mail', $mail);
$app->instance(\Core\Mail\MailManager::class, $mail);

// Queue
$queue = new \Core\Queue\QueueManager($config->load('queue'), $db);
$app->instance('queue', $queue);
$app->instance(\Core\Queue\QueueManager::class, $queue);

// I18n
$translator = new \Core\I18n\Translator(
    locale:   $config->get('app.locale', 'tr'),
    fallback: $config->get('app.fallback_locale', 'en'),
    path:     BASE_PATH . '/lang',
);
$app->instance('translator', $translator);
$app->instance(\Core\I18n\Translator::class, $translator);

// Cache
$cache = new \Core\Cache\CacheManager($config->load('cache'));
$app->instance('cache', $cache);
$app->instance(\Core\Cache\CacheManager::class, $cache);

// Router
$router = new Router();
$app->instance('router', $router);
$app->instance(Router::class, $router);

return $app;