<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;

class MakeCrudCommand extends Command
{
    protected string $signature = 'make:crud {module} {resource}';
    protected string $description = 'Generate CRUD scaffold (model/controller/views/routes) inside a module';

    public function handle(): int
    {
        $module = $this->toStudly((string) $this->argument('module', ''));
        $resource = $this->toStudly((string) $this->argument('resource', ''));

        if ($module === '' || $resource === '') {
            $this->error('Module and resource are required.');
            $this->line('Usage: php framework make:crud Catalog Product');
            return 1;
        }

        $modulePath = BASE_PATH . '/modules/' . $module;
        if (!is_dir($modulePath)) {
            $this->error("Module not found: modules/{$module}");
            $this->line("Create it first: php framework make:module {$module}");
            return 1;
        }

        $resourcePlural = $this->pluralize(strtolower($resource));
        $routeName = $resourcePlural;
        $controllerClass = $resource . 'Controller';
        $modelClass = $resource;
        $viewDir = $modulePath . '/Views/' . $resourcePlural;

        $this->makeDirectory($modulePath . '/Controllers');
        $this->makeDirectory($modulePath . '/Models');
        $this->makeDirectory($viewDir);
        $this->makeDirectory($modulePath . '/routes');

        $controllerPath = $modulePath . '/Controllers/' . $controllerClass . '.php';
        if (!is_file($controllerPath)) {
            file_put_contents($controllerPath, $this->controllerTemplate($module, $controllerClass, $modelClass));
            $this->info("Created: modules/{$module}/Controllers/{$controllerClass}.php");
        }

        $modelPath = $modulePath . '/Models/' . $modelClass . '.php';
        if (!is_file($modelPath)) {
            $table = $this->pluralize(strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $resource) ?? $resource));
            file_put_contents($modelPath, $this->modelTemplate($module, $modelClass, $table));
            $this->info("Created: modules/{$module}/Models/{$modelClass}.php");
        }

        $this->ensureView($viewDir . '/index.php', $this->viewTemplate('Index', $module, $resource));
        $this->ensureView($viewDir . '/create.php', $this->viewTemplate('Create', $module, $resource));
        $this->ensureView($viewDir . '/edit.php', $this->viewTemplate('Edit', $module, $resource));

        $webRoutesPath = $modulePath . '/routes/web.php';
        if (!is_file($webRoutesPath)) {
            file_put_contents($webRoutesPath, "<?php\n\ndeclare(strict_types=1);\n\n/** @var \\Core\\Routing\\Router \$router */\n\n");
        }

        $marker = "// kirpi-crud:{$routeName}";
        $webRoutesContent = (string) file_get_contents($webRoutesPath);
        if (!str_contains($webRoutesContent, $marker)) {
            $webRoutesContent .= "\n{$marker}\n";
            $webRoutesContent .= "\$router->adminResource('{$routeName}', \\Modules\\{$module}\\Controllers\\{$controllerClass}::class);\n";
            file_put_contents($webRoutesPath, $webRoutesContent);
            $this->info("Updated: modules/{$module}/routes/web.php");
        } else {
            $this->warning("Skip routes/web.php, marker already exists: {$marker}");
        }

        $this->success("CRUD scaffold ready: {$module}/{$resource}");

        return 0;
    }

    private function toStudly(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9]+/', ' ', $value) ?? $value;
        $value = str_replace(' ', '', ucwords(strtolower(trim($value))));
        return $value;
    }

    private function pluralize(string $value): string
    {
        if (str_ends_with($value, 'y')) {
            return substr($value, 0, -1) . 'ies';
        }
        if (str_ends_with($value, 's')) {
            return $value . 'es';
        }
        return $value . 's';
    }

    private function makeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    private function ensureView(string $path, string $content): void
    {
        if (!is_file($path)) {
            file_put_contents($path, $content);
            $relative = str_replace(BASE_PATH . DIRECTORY_SEPARATOR, '', $path);
            $relative = str_replace('\\', '/', $relative);
            $this->info("Created: {$relative}");
        }
    }

    private function modelTemplate(string $module, string $modelClass, string $table): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace Modules\\{$module}\\Models;

use Core\\Model\\Model;

class {$modelClass} extends Model
{
    protected string \$table = '{$table}';
}

PHP;
    }

    private function controllerTemplate(string $module, string $controllerClass, string $modelClass): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace Modules\\{$module}\\Controllers;

use Core\\Http\\Request;
use Core\\Http\\Response;
use Modules\\{$module}\\Models\\{$modelClass};

class {$controllerClass}
{
    public function index(): Response
    {
        return Response::json(['ok' => true, 'data' => {$modelClass}::all()->toArray()]);
    }

    public function create(): Response
    {
        return Response::json(['ok' => true, 'action' => 'create-form']);
    }

    public function store(Request \$request): Response
    {
        \$item = {$modelClass}::create(\$request->all());
        return Response::json(['ok' => true, 'data' => \$item->toArray()], 201);
    }

    public function show(Request \$request): Response
    {
        \$item = {$modelClass}::findOrFail((int) \$request->route('id'));
        return Response::json(['ok' => true, 'data' => \$item->toArray()]);
    }

    public function edit(Request \$request): Response
    {
        \$item = {$modelClass}::findOrFail((int) \$request->route('id'));
        return Response::json(['ok' => true, 'action' => 'edit-form', 'data' => \$item->toArray()]);
    }

    public function update(Request \$request): Response
    {
        \$item = {$modelClass}::findOrFail((int) \$request->route('id'));
        \$item->update(\$request->all());
        return Response::json(['ok' => true, 'data' => \$item->fresh()->toArray()]);
    }

    public function destroy(Request \$request): Response
    {
        \$item = {$modelClass}::findOrFail((int) \$request->route('id'));
        \$item->delete();
        return Response::json(['ok' => true]);
    }
}

PHP;
    }

    private function viewTemplate(string $title, string $module, string $resource): string
    {
        return <<<PHP
<?php
declare(strict_types=1);
?>
<div class="card">
  <div class="card-header">
    <h3 class="card-title">{$title} {$resource}</h3>
  </div>
  <div class="card-body">
    <p class="text-secondary mb-0">Generated scaffold view ({$module}/{$resource}).</p>
  </div>
</div>

PHP;
    }
}
