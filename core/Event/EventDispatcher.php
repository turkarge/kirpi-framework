<?php

declare(strict_types=1);

namespace Core\Event;

class EventDispatcher
{
    private array $listeners   = [];
    private array $subscribers = [];
    private array $wildcards   = [];

    // ─── Listener Kayıt ──────────────────────────────────────

    public function listen(string $event, string|array|\Closure $listener): void
    {
        if (is_array($listener)) {
            foreach ($listener as $l) {
                $this->listen($event, $l);
            }
            return;
        }

        if (str_contains($event, '*')) {
            $this->wildcards[$event][] = $listener;
            return;
        }

        $this->listeners[$event][] = $listener;
    }

    public function subscribe(string $subscriber): void
    {
        $instance = new $subscriber();

        if (!method_exists($instance, 'subscribe')) {
            throw new \RuntimeException("Subscriber [{$subscriber}] must have a subscribe() method.");
        }

        $instance->subscribe($this);
        $this->subscribers[] = $subscriber;
    }

    public function forget(string $event): void
    {
        unset($this->listeners[$event]);
    }

    // ─── Dispatch ────────────────────────────────────────────

    public function dispatch(Event|string $event, array $payload = []): void
    {
        if (is_string($event)) {
            $eventName = $event;
            $eventObj  = null;
        } else {
            $eventName = get_class($event);
            $eventObj  = $event;
        }

        // Direkt listener'lar
        foreach ($this->getListeners($eventName) as $listener) {
            if ($eventObj?->isPropagationStopped()) break;

            $this->callListener($listener, $eventObj ?? $payload);
        }
    }

    public function dispatchIf(bool $condition, Event|string $event, array $payload = []): void
    {
        if ($condition) {
            $this->dispatch($event, $payload);
        }
    }

    public function dispatchUnless(bool $condition, Event|string $event, array $payload = []): void
    {
        if (!$condition) {
            $this->dispatch($event, $payload);
        }
    }

    // ─── Until — false dönene kadar çalıştır ─────────────────

    public function until(Event|string $event, array $payload = []): mixed
    {
        $eventName = is_string($event) ? $event : get_class($event);
        $eventObj  = is_object($event) ? $event : null;

        foreach ($this->getListeners($eventName) as $listener) {
            $result = $this->callListener($listener, $eventObj ?? $payload);

            if ($result === false) {
                return false;
            }
        }

        return null;
    }

    // ─── Has ─────────────────────────────────────────────────

    public function hasListeners(string $event): bool
    {
        return !empty($this->getListeners($event));
    }

    // ─── Private ─────────────────────────────────────────────

    private function getListeners(string $event): array
    {
        $listeners = $this->listeners[$event] ?? [];

        // Wildcard listener'ları ekle
        foreach ($this->wildcards as $pattern => $wildcardListeners) {
            if (fnmatch($pattern, $event)) {
                $listeners = array_merge($listeners, $wildcardListeners);
            }
        }

        return $listeners;
    }

    private function callListener(string|\Closure $listener, mixed $payload): mixed
    {
        if ($listener instanceof \Closure) {
            return $listener($payload);
        }

        // "ClassName@method" formatı
        if (str_contains($listener, '@')) {
            [$class, $method] = explode('@', $listener, 2);
            $instance = app($class);
            return $instance->$method($payload);
        }

        // Sadece class adı — handle() metodunu çağır
        $instance = app($listener);

        if ($instance instanceof Listener && $instance->shouldQueue()) {
            // Queue'ya at — Queue sistemi eklenince aktif olacak
            // app(\Core\Queue\QueueManager::class)->push($listener, $payload);
            return null;
        }

        return $instance->handle($payload);
    }
}