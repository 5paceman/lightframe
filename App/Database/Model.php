<?php

namespace App\Database;

abstract class Model {
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $attributes = [];

    public function __construct(array $data = []) {
        foreach ($data as $key => $value) {
            $this->attributes[$key] = $value;
        }
    }

    public function __get($name) {
        return $this->attributes[$name] ?? null;
    }

    public function __set($name, $value) {
        $this->attributes[$name] = $value;
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
    public static function where(string $column, string $operator, $value): ?static {
        $data = static::query()->where($column, $operator, $value)->first();
        return $data ? new static($data) : null;
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