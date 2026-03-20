<?php

declare(strict_types=1);

namespace Core\Console;

abstract class Command
{
    protected string $signature   = '';
    protected string $description = '';

    private array $arguments = [];
    private array $options   = [];
    private array $input     = [];

    abstract public function handle(): int;

    // ─── Input ───────────────────────────────────────────────

    public function setInput(array $argv): void
    {
        $this->input = $argv;
        $this->parseInput($argv);
    }

    public function argument(string $name, mixed $default = null): mixed
    {
        return $this->arguments[$name] ?? $default;
    }

    public function option(string $name, mixed $default = null): mixed
    {
        return $this->options[$name] ?? $default;
    }

    public function arguments(): array
    {
        return $this->arguments;
    }

    public function options(): array
    {
        return $this->options;
    }

    // ─── Output ──────────────────────────────────────────────

    public function info(string $message): void
    {
        echo "\033[32m{$message}\033[0m" . PHP_EOL;
    }

    public function error(string $message): void
    {
        echo "\033[31mERROR: {$message}\033[0m" . PHP_EOL;
    }

    public function warning(string $message): void
    {
        echo "\033[33mWARNING: {$message}\033[0m" . PHP_EOL;
    }

    public function line(string $message = ''): void
    {
        echo $message . PHP_EOL;
    }

    public function comment(string $message): void
    {
        echo "\033[33m{$message}\033[0m" . PHP_EOL;
    }

    public function success(string $message): void
    {
        echo "\033[32m✅ {$message}\033[0m" . PHP_EOL;
    }

    public function table(array $headers, array $rows): void
    {
        // Kolon genişliklerini hesapla
        $widths = array_map('strlen', $headers);

        foreach ($rows as $row) {
            foreach (array_values($row) as $i => $cell) {
                $widths[$i] = max($widths[$i] ?? 0, strlen((string) $cell));
            }
        }

        $separator = '+' . implode('+', array_map(fn($w) => str_repeat('-', $w + 2), $widths)) . '+';

        // Header
        $this->line($separator);
        $header = '|';
        foreach ($headers as $i => $h) {
            $header .= ' ' . str_pad($h, $widths[$i]) . ' |';
        }
        $this->line($header);
        $this->line($separator);

        // Rows
        foreach ($rows as $row) {
            $line = '|';
            foreach (array_values($row) as $i => $cell) {
                $line .= ' ' . str_pad((string) $cell, $widths[$i]) . ' |';
            }
            $this->line($line);
        }

        $this->line($separator);
    }

    public function ask(string $question, mixed $default = null): string
    {
        echo "\033[36m{$question}\033[0m";
        if ($default !== null) echo " [{$default}]";
        echo ': ';

        $input = trim(fgets(STDIN));
        return $input ?: (string) $default;
    }

    public function confirm(string $question, bool $default = false): bool
    {
        $hint = $default ? '[Y/n]' : '[y/N]';
        echo "\033[36m{$question} {$hint}\033[0m: ";

        $input = strtolower(trim(fgets(STDIN)));

        if (empty($input)) return $default;

        return in_array($input, ['y', 'yes', 'evet', 'e']);
    }

    // ─── Getters ─────────────────────────────────────────────

    public function getSignature(): string   { return $this->signature; }
    public function getDescription(): string { return $this->description; }

    // ─── Parse ───────────────────────────────────────────────

    private function parseInput(array $argv): void
    {
        // İlk iki argümanı atla (script adı ve komut adı)
        $params = array_slice($argv, 2);

        $argIndex = 0;
        $sigArgs  = $this->parseSignature();

        foreach ($params as $param) {
            if (str_starts_with($param, '--')) {
                // Option: --name=value veya --flag
                $param = substr($param, 2);
                if (str_contains($param, '=')) {
                    [$key, $value] = explode('=', $param, 2);
                    $this->options[$key] = $value;
                } else {
                    $this->options[$param] = true;
                }
            } else {
                // Argument
                $argName = $sigArgs[$argIndex] ?? "arg{$argIndex}";
                $this->arguments[$argName] = $param;
                $argIndex++;
            }
        }
    }

    private function parseSignature(): array
    {
        preg_match_all('/\{(\w+)\}/', $this->signature, $matches);
        return $matches[1] ?? [];
    }
}