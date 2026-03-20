<?php

declare(strict_types=1);

namespace Core\Model\Concerns;

trait HasEvents
{
    private static array $listeners = [];

    public static function creating(\Closure $callback): void  { static::on('creating', $callback); }
    public static function created(\Closure $callback): void   { static::on('created', $callback); }
    public static function updating(\Closure $callback): void  { static::on('updating', $callback); }
    public static function updated(\Closure $callback): void   { static::on('updated', $callback); }
    public static function deleting(\Closure $callback): void  { static::on('deleting', $callback); }
    public static function deleted(\Closure $callback): void   { static::on('deleted', $callback); }
    public static function saving(\Closure $callback): void    { static::on('saving', $callback); }
    public static function saved(\Closure $callback): void     { static::on('saved', $callback); }

    public static function on(string $event, \Closure $callback): void
    {
        static::$listeners[static::class][$event][] = $callback;
    }

    protected function fireEvent(string $event): bool
    {
        foreach (static::$listeners[static::class][$event] ?? [] as $listener) {
            if ($listener($this) === false) {
                return false;
            }
        }
        return true;
    }
}