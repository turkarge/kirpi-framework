<?php

declare(strict_types=1);

namespace Core\Model;

use Core\Database\DatabaseManager;
use Core\Database\QueryBuilder;
use Core\Model\Concerns\HasRelationships;
use Core\Model\Concerns\HasCasts;
use Core\Model\Concerns\HasScopes;
use Core\Model\Concerns\HasEvents;
use Core\Model\Concerns\HasSerialization;

abstract class Model
{
    use HasRelationships;
    use HasCasts;
    use HasScopes;
    use HasEvents;
    use HasSerialization;

    // ─── Yapılandırma ────────────────────────────────────────
    protected string  $table;
    protected string  $primaryKey   = 'id';
    protected bool    $incrementing = true;
    protected bool    $timestamps   = true;
    protected ?string $connection   = null;
    protected array   $fillable     = [];
    protected array   $guarded      = ['id'];
    protected array   $hidden       = [];
    protected array   $appends      = [];
    protected array   $casts        = [];

    // ─── State ───────────────────────────────────────────────
    protected array $attributes         = [];
    protected array $original           = [];
    protected array $relations          = [];
    protected bool  $exists             = false;
    protected bool  $wasRecentlyCreated = false;

    public function __construct(array $attributes = [])
    {
        $this->bootIfNotBooted();
        $this->fill($attributes);
    }

    // ─── Magic ───────────────────────────────────────────────

    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    public function __isset(string $key): bool
    {
        return $this->getAttribute($key) !== null;
    }

    // ─── Attribute ───────────────────────────────────────────

    public function getAttribute(string $key): mixed
    {
        // 1. Relation yüklenmiş mi?
        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }

        // 2. Accessor var mı?
        $accessor = 'get' . str_replace('_', '', ucwords($key, '_')) . 'Attribute';
        if (method_exists($this, $accessor)) {
            return $this->$accessor($this->attributes[$key] ?? null);
        }

        // 3. Cast var mı?
        if (isset($this->casts[$key]) && array_key_exists($key, $this->attributes)) {
            return $this->castGet($key, $this->attributes[$key]);
        }

        // 4. Appended attribute?
        if (in_array($key, $this->appends)) {
            $method = 'get' . str_replace('_', '', ucwords($key, '_')) . 'Attribute';
            return method_exists($this, $method) ? $this->$method(null) : null;
        }

        // 5. Relation method?
        if (method_exists($this, $key)) {
            $relation = $this->$key();
            if ($relation instanceof Relations\Relation) {
                return $this->relations[$key] = $relation->getResults();
            }
        }

        return $this->attributes[$key] ?? null;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $mutator = 'set' . str_replace('_', '', ucwords($key, '_')) . 'Attribute';
        if (method_exists($this, $mutator)) {
            $this->$mutator($value);
            return;
        }

        if (isset($this->casts[$key])) {
            $value = $this->castSet($key, $value);
        }

        $this->attributes[$key] = $value;
    }

    // ─── Fill ────────────────────────────────────────────────

    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }
        return $this;
    }

    public function forceFill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    private function isFillable(string $key): bool
    {
        if (in_array($key, $this->guarded)) return false;
        if (empty($this->fillable))         return true;
        return in_array($key, $this->fillable);
    }

    // ─── Dirty ───────────────────────────────────────────────

    public function isDirty(string ...$keys): bool
    {
        $keys = empty($keys) ? array_keys($this->attributes) : $keys;

        foreach ($keys as $key) {
            if (($this->attributes[$key] ?? null) !== ($this->original[$key] ?? null)) {
                return true;
            }
        }

        return false;
    }

    public function isClean(string ...$keys): bool
    {
        return !$this->isDirty(...$keys);
    }

    public function getDirty(): array
    {
        return array_filter(
            $this->attributes,
            fn($value, $key) => $value !== ($this->original[$key] ?? null),
            ARRAY_FILTER_USE_BOTH
        );
    }

    // ─── CRUD ────────────────────────────────────────────────

    public function save(): bool
    {
        return $this->exists ? $this->performUpdate() : $this->performInsert();
    }

    private function performInsert(): bool
    {
        if (!$this->fireEvent('creating')) return false;
        if (!$this->fireEvent('saving'))   return false;

        if ($this->timestamps) {
            $this->attributes['created_at'] = now();
            $this->attributes['updated_at'] = now();
        }

        $id = static::query()->insert($this->attributes);

        if ($this->incrementing) {
            $this->attributes[$this->primaryKey] = $id;
        }

        $this->original           = $this->attributes;
        $this->exists             = true;
        $this->wasRecentlyCreated = true;

        $this->fireEvent('created');
        $this->fireEvent('saved');

        return true;
    }

    private function performUpdate(): bool
    {
        if (!$this->fireEvent('updating')) return false;
        if (!$this->fireEvent('saving'))   return false;

        $dirty = $this->getDirty();

        if (empty($dirty)) return true;

        if ($this->timestamps) {
            $dirty['updated_at']            = now();
            $this->attributes['updated_at'] = $dirty['updated_at'];
        }

        static::query()
            ->where($this->primaryKey, $this->getKey())
            ->update($dirty);

        $this->original = $this->attributes;

        $this->fireEvent('updated');
        $this->fireEvent('saved');

        return true;
    }

    public function delete(): bool
    {
        if (!$this->fireEvent('deleting')) return false;

        if (in_array('deleted_at', array_keys($this->attributes))) {
            $this->attributes['deleted_at'] = now();
            $this->save();
        } else {
            static::query()
                ->where($this->primaryKey, $this->getKey())
                ->delete();
        }

        $this->exists = false;
        $this->fireEvent('deleted');

        return true;
    }

    public function update(array $attributes): bool
    {
        return $this->fill($attributes)->save();
    }

    // ─── Static CRUD ─────────────────────────────────────────

    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    public static function find(int|string $id): ?static
    {
        return static::query()->find($id);
    }

    public static function findOrFail(int|string $id): static
    {
        $result = static::find($id);

        if ($result === null) {
            throw new \Core\Exception\NotFoundException(
                "Model [" . static::class . "] with ID [{$id}] not found."
            );
        }

        return $result;
    }

    public static function all(): \Core\Database\Result\Collection
    {
        return static::query()->get();
    }

    public static function where(string $column, mixed $operator, mixed $value = null): QueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }

    public static function first(): ?static
    {
        return static::query()->first();
    }

    // ─── Query Builder ───────────────────────────────────────

    public static function query(): QueryBuilder
{
    $instance = new static();

    return app(DatabaseManager::class)
        ->table($instance->getTable())
        ->setModel(static::class);
}

    // ─── DB'den Model Oluştur ────────────────────────────────

    public function newFromDatabase(array $attributes): static
    {
        $model = new static();
        $model->attributes = $attributes;
        $model->original   = $attributes;
        $model->exists     = true;
        return $model;
    }

    // ─── Helpers ─────────────────────────────────────────────

    public function getKey(): mixed
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }

    public function getTable(): string
    {
        if (isset($this->table)) {
            return $this->table;
        }

        $class = class_basename(static::class);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class)) . 's';
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function wasRecentlyCreated(): bool
    {
        return $this->wasRecentlyCreated;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function fresh(): static
    {
        return static::findOrFail($this->getKey());
    }

    public function refresh(): static
    {
        $fresh            = $this->fresh();
        $this->attributes = $fresh->attributes;
        $this->original   = $fresh->original;
        $this->relations  = [];
        return $this;
    }

    // ─── Boot ────────────────────────────────────────────────

    private static array $booted = [];

    private function bootIfNotBooted(): void
    {
        if (!isset(static::$booted[static::class])) {
            static::$booted[static::class] = true;
            static::boot();
        }
    }

    protected static function boot(): void
    {
        // Alt sınıflar override edebilir
    }

    public static function make(array $attributes = []): static
    {
        return new static($attributes);
    }
}