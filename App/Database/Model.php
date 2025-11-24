<?php

namespace App\Database;

use ArrayAccess;

abstract class Model implements ArrayAccess{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $attributes = [];

    public function __construct(array $data = []) {
        foreach ($data as $key => $value) {
            $this->attributes[$key] = $value;
        }
    }

    public function offsetExists($offset): bool {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet($offset): mixed {
        return $this->attributes[$offset] ?? null;
    }

    public function __get($name) {
        return $this->attributes[$name] ?? null;
    }

    public function __set($name, $value) {
        $this->attributes[$name] = $value;
    }

    public function offsetSet($offset, $value): void {
        $this->attributes[$offset] = $value;
    }

    public function offsetUnset($offset): void {
        unset($this->attributes[$offset]);
    }

    protected static function query(): QueryBuilder {
        $pdo = Database::get()->connect();
        return (new QueryBuilder($pdo))->table((new static)->table);
    }

    // Find by primary key
    public static function find($id): ?static {
        $data = static::query()->where((new static)->primaryKey, '=', $id)->first();
        return $data ? new static($data) : null;
    }

    // Get all records
    public static function all(): array {
        $rows = static::query()->get();
        return array_map(fn($row) => new static($row), $rows);
    }

    // Where helper for chaining
    public static function where(string $column, string $operator, $value): QueryBuilder {
        return static::query()->where($column, $operator, $value);
    }

    public static function create(array $data)
    {
        $qb = static::query();
        if($qb->insert($data))
            return static::find($qb->lastInsertId());
        else
            return false;
    }

    // Save (insert or update)
    public function save(): bool {
        $qb = static::query();

        if (isset($this->attributes[$this->primaryKey])) {
            // Update
            $qb->where($this->primaryKey, '=', $this->attributes[$this->primaryKey]);
            return $qb->update($this->attributes);
        }

        // Insert
        $result = $qb->insert($this->attributes);
        if ($result) {
            $this->attributes[$this->primaryKey] = $qb->lastInsertId();
        }
        return $result;
    }

    public function delete(): bool {
        if (!isset($this->attributes[$this->primaryKey])) return false;

        return static::query()
            ->where($this->primaryKey, '=', $this->attributes[$this->primaryKey])
            ->update([$this->primaryKey => null]); // Or implement actual delete method in QueryBuilder
    }

}

?>