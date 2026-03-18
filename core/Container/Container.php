<?php

declare(strict_types=1);

namespace Core\Container;

use Closure;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

class Container
{
    // Tek instance (singleton pattern)
    private static ?self $instance = null;

    // Binding kayıtları
    private array $bindings   = [];

    // Singleton instance'ları
    private array $instances  = [];

    // Alias kayıtları
    private array $aliases    = [];

    // Build stack — circular dependency tespiti
    private array $buildStack = [];

    // ─── Singleton Access ────────────────────────────────────

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public static function setInstance(self $container): void
    {
        static::$instance = $container;
    }

    // ─── Binding ─────────────────────────────────────────────

    public function bind(string $abstract, Closure|string|null $concrete = null): void
    {
        $concrete ??= $abstract;

        $this->bindings[$abstract] = [
            'concrete'  => $concrete,
            'singleton' => false,
        ];
    }

    public function singleton(string $abstract, Closure|string|null $concrete = null): void
    {
        $concrete ??= $abstract;

        $this->bindings[$abstract] = [
            'concrete'  => $concrete,
            'singleton' => true,
        ];
    }

    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }

    // ─── Çözümleme ───────────────────────────────────────────

    public function make(string $abstract, array $parameters = []): mixed
    {
        // Alias çöz
        $abstract = $this->getAlias($abstract);

        // Singleton instance varsa döndür
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Concrete'i belirle
        $concrete = $this->getConcrete($abstract);

        // Build et
        $object = $this->build($concrete, $parameters);

        // Singleton ise kaydet
        if ($this->isSingleton($abstract)) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    public function build(Closure|string $concrete, array $parameters = []): mixed
    {
        // Closure ise direkt çalıştır
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        // Circular dependency kontrolü
        if (in_array($concrete, $this->buildStack)) {
            throw new ContainerException(
                "Circular dependency detected: " . implode(' → ', $this->buildStack) . " → {$concrete}"
            );
        }

        $this->buildStack[] = $concrete;

        try {
            $reflector = new ReflectionClass($concrete);
        } catch (\ReflectionException $e) {
            array_pop($this->buildStack);
            throw new ContainerException("Class [{$concrete}] does not exist.", 0, $e);
        }

        if (!$reflector->isInstantiable()) {
            array_pop($this->buildStack);
            throw new ContainerException("Class [{$concrete}] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        // Constructor yoksa direkt oluştur
        if ($constructor === null) {
            array_pop($this->buildStack);
            return new $concrete();
        }

        // Constructor parametrelerini çöz
        $dependencies = $this->resolveDependencies(
            $constructor->getParameters(),
            $parameters
        );

        array_pop($this->buildStack);

        return $reflector->newInstanceArgs($dependencies);
    }

    // ─── Dependency Çözümleme ────────────────────────────────

    private function resolveDependencies(array $params, array $primitives = []): array
    {
        $dependencies = [];

        foreach ($params as $param) {
            // Manuel verilen parametre var mı?
            if (array_key_exists($param->getName(), $primitives)) {
                $dependencies[] = $primitives[$param->getName()];
                continue;
            }

            $type = $param->getType();

            // Tip yoksa veya primitive ise
            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                $dependencies[] = $this->resolvePrimitive($param);
                continue;
            }

            // Class/Interface ise Container'dan çöz
            try {
                $dependencies[] = $this->make($type->getName());
            } catch (ContainerException $e) {
                if ($param->isDefaultValueAvailable()) {
                    $dependencies[] = $param->getDefaultValue();
                } else {
                    throw $e;
                }
            }
        }

        return $dependencies;
    }

    private function resolvePrimitive(ReflectionParameter $param): mixed
    {
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        if ($param->allowsNull()) {
            return null;
        }

        throw new ContainerException(
            "Cannot resolve primitive parameter [{$param->getName()}]."
        );
    }

    // ─── Helpers ─────────────────────────────────────────────

    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract])
            || isset($this->instances[$abstract])
            || isset($this->aliases[$abstract]);
    }

    public function has(string $abstract): bool
    {
        return $this->bound($abstract);
    }

    public function resolved(string $abstract): bool
    {
        return isset($this->instances[$this->getAlias($abstract)]);
    }

    public function flush(): void
    {
        $this->bindings   = [];
        $this->instances  = [];
        $this->aliases    = [];
        $this->buildStack = [];
    }

    private function getConcrete(string $abstract): Closure|string
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    private function isSingleton(string $abstract): bool
    {
        return isset($this->bindings[$abstract])
            && $this->bindings[$abstract]['singleton'] === true;
    }

    private function getAlias(string $abstract): string
    {
        return $this->aliases[$abstract] ?? $abstract;
    }

    // ─── Magic ───────────────────────────────────────────────

    public function __get(string $abstract): mixed
    {
        return $this->make($abstract);
    }

    public function offsetExists(mixed $offset): bool  { return $this->bound($offset); }
    public function offsetGet(mixed $offset): mixed    { return $this->make($offset); }
    public function offsetSet(mixed $offset, mixed $value): void { $this->instance($offset, $value); }
    public function offsetUnset(mixed $offset): void   { unset($this->bindings[$offset], $this->instances[$offset]); }
}