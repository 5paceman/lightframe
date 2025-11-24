<?php

namespace App\Database;

class ColumnDefinition
{
    public string $name;
    public string $type;
    public bool $nullable = false;
    public mixed $default = null;
    public bool $unsigned = false;
    public string $extra = '';
    public ?string $references = null;
    public ?string $on = null;

    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    // Fluent modifiers -------------------

    public function nullable(bool $value = true): static
    {
        $this->nullable = $value;
        return $this;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;
        return $this;
    }

    public function unsigned(bool $value = true): static
    {
        $this->unsigned = $value;
        return $this;
    }

    public function extra(string $extra): static
    {
        $this->extra = $extra;
        return $this;
    }

    public function references(string $column): static
    {
        $this->references = $column;
        return $this;
    }

    public function on(string $table): static
    {
        $this->on = $table;
        return $this;
    }
}

class IndexDefinition
{
    public array $columns = [];
    public string $type; // 'index', 'unique', 'primary', 'foreign'
    public ?string $foreignTable = null;
    public ?string $foreignColumn = null;

    public function __construct(string $type, array $columns)
    {
        $this->type = $type;
        $this->columns = $columns;
    }

    public function references(string $column): static
    {
        $this->foreignColumn = $column;
        return $this;
    }

    public function on(string $table): static
    {
        $this->foreignTable = $table;
        return $this;
    }
}

class TableBuilder
{
    protected \PDO $pdo;
    protected string $driver;
    protected string $table;

    /** @var ColumnDefinition[] */
    protected array $columns = [];

    /** @var IndexDefinition[] */
    protected array $indexes = [];

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    public function name(string $table): static
    {
        $this->table = $table;
        return $this;
    }
    
    // --------------------------------------------------------------
    // Column Type Maps (MySQL ↔ PostgreSQL)
    // --------------------------------------------------------------

    private function mapType(string $type, ?int $length = null): string
    {
        $map = [
            'mysql' => [
                'int' => 'INT',
                'bigint' => 'BIGINT',
                'string' => "VARCHAR($length)",
                'text' => 'TEXT',
                'boolean' => 'TINYINT(1)',
                'json' => 'JSON',
            ],
            'pgsql' => [
                'int' => 'INTEGER',
                'bigint' => 'BIGINT',
                'string' => "VARCHAR($length)",
                'text' => 'TEXT',
                'boolean' => 'BOOLEAN',
                'json' => 'JSONB',
            ],
        ];

        return $map[$this->driver][$type];
    }

    // --------------------------------------------------------------
    // Column Definitions
    // --------------------------------------------------------------

    protected function addColumn(ColumnDefinition $col): static
    {
        $this->columns[] = $col;
        return $this;
    }

    public function increments(string $name): static
    {
        $type = $this->driver === 'pgsql'
            ? 'SERIAL'
            : 'INT AUTO_INCREMENT';

        $col = new ColumnDefinition($name, $type);
        $this->addColumn($col);

        $this->indexes[] = new IndexDefinition('primary', [$name]);

        return $this;
    }

    public function integer(string $name): ColumnDefinition
    {
        $col = new ColumnDefinition($name, $this->mapType('int'));
        $this->addColumn($col);
        return $col;
    }

    public function bigInteger(string $name): ColumnDefinition
    {
        $col = new ColumnDefinition($name, $this->mapType('bigint'));
        $this->addColumn($col);
        return $col;
    }

    public function string(string $name, int $length = 255): ColumnDefinition
    {
        $col = new ColumnDefinition($name, $this->mapType('string', $length));
        $this->addColumn($col);
        return $col;
    }

    public function text(string $name): ColumnDefinition
    {
        $col = new ColumnDefinition($name, $this->mapType('text'));
        $this->addColumn($col);
        return $col;
    }

    public function boolean(string $name): ColumnDefinition
    {
        $col = new ColumnDefinition($name, $this->mapType('boolean'));
        $this->addColumn($col);
        return $col;
    }

    public function json(string $name): ColumnDefinition
    {
        $col = new ColumnDefinition($name, $this->mapType('json'));
        $this->addColumn($col);
        return $col;
    }

    public function enum(string $name, array $values): ColumnDefinition
    {
        $type = $this->driver === 'mysql'
            ? "ENUM(" . implode(',', array_map(fn($v) => $this->pdo->quote($v), $values)) . ")"
            : "TEXT CHECK ($name IN (" . implode(',', array_map(fn($v) => $this->pdo->quote($v), $values)) . "))";

        $col = new ColumnDefinition($name, $type);
        $this->addColumn($col);
        return $col;
    }

    public function foreignId(string $name): IndexDefinition
    {
        $col = new ColumnDefinition($name, $this->mapType('bigint'));
        $col->unsigned();
        $this->addColumn($col);

        $index = new IndexDefinition('foreign', [$name]);
        $this->indexes[] = $index;

        return $index;
    }

    public function timestamp(string $name, $default = null)
    {
        $created = new ColumnDefinition($name, 'TIMESTAMP');
        $created->nullable(true);
        $created->default($default);
        $this->addColumn($created);
        return $created;
    }

    public function timestamps(): static
    {
        // created_at column
        $created = new ColumnDefinition('created_at', 'TIMESTAMP');
        $created->nullable(true);
        $created->default('CURRENT_TIMESTAMP');
        $this->addColumn($created);

        // updated_at column
        $updated = new ColumnDefinition('updated_at', 'TIMESTAMP');
        $updated->nullable(true);

        if ($this->driver === 'mysql') {
            // MySQL supports explicit ON UPDATE
            $updated->default('CURRENT_TIMESTAMP');
            $updated->extra('ON UPDATE CURRENT_TIMESTAMP');
        } else {
            // PostgreSQL: use default only
            // (trigger-based auto-update can be added if desired)
            $updated->default('CURRENT_TIMESTAMP');
        }

        $this->addColumn($updated);

        return $this;
    }

    // --------------------------------------------------------------
    // Index / Constraints
    // --------------------------------------------------------------

    public function primary(array $columns): static
    {
        $this->indexes[] = new IndexDefinition('primary', $columns);
        return $this;
    }

    public function unique(array $columns): static
    {
        $this->indexes[] = new IndexDefinition('unique', $columns);
        return $this;
    }

    public function index(array $columns): static
    {
        $this->indexes[] = new IndexDefinition('index', $columns);
        return $this;
    }

    // --------------------------------------------------------------
    // SQL Compilation
    // --------------------------------------------------------------

    private function wrap(string $id): string
    {
        return match ($this->driver) {
            'mysql' => "`$id`",
            'pgsql' => "\"$id\"",
            default => $id,
        };
    }

    private function compileColumn(ColumnDefinition $col): string
    {
        $sql = $this->wrap($col->name) . " " . $col->type;

        if ($col->unsigned && $this->driver === 'mysql') {
            $sql .= " UNSIGNED";
        }

        $sql .= $col->nullable ? " NULL" : " NOT NULL";

        if ($col->default !== null) {
            if (in_array($col->default, ['CURRENT_TIMESTAMP'])) {
                // SQL expression — do not quote
                $sql .= " DEFAULT {$col->default}";
            } else {
                $value = is_numeric($col->default)
                    ? $col->default
                    : $this->pdo->quote($col->default);

                $sql .= " DEFAULT $value";
            }
        }

        if ($col->extra) {
            $sql .= " " . $col->extra;
        }

        return $sql;
    }

    private function compileIndex(IndexDefinition $index): string
    {
        $cols = implode(", ", array_map(fn($c) => $this->wrap($c), $index->columns));

        return match ($index->type) {
            'primary' => "PRIMARY KEY ($cols)",
            'unique'  => "UNIQUE ($cols)",
            'index'   => $this->driver === 'mysql' ? "INDEX ($cols)" : throw new \RuntimeException("PostgreSQL regular index handled separately"),
            'foreign' => $index->foreignTable && $index->foreignColumn
                ? "FOREIGN KEY ($cols) REFERENCES "
                . $this->wrap($index->foreignTable)
                . " (" . $this->wrap($index->foreignColumn) . ")"
                : throw new \RuntimeException("Foreign key requires both table and column"),
            default   => throw new \RuntimeException("Unknown index type: {$index->type}")
        };
    }

    public function drop(): bool
    {
        $sql = "DROP TABLE IF EXISTS {$this->table};";
        return $this->pdo->exec($sql) !== false;
    }

    // --------------------------------------------------------------
    // Create Table
    // --------------------------------------------------------------

    public function create(): bool
    {
        // Compile columns
        $columnSql = array_map(fn($c) => $this->compileColumn($c), $this->columns);

        // Compile only constraints allowed inside CREATE TABLE (PK, UNIQUE, FK)
        $tableIndexes = array_filter(array_map(function ($i) {
            if ($i->type === 'index' && $this->driver === 'pgsql') {
                // Skip regular indexes in PostgreSQL here
                return null;
            }
            return $this->compileIndex($i);
        }, $this->indexes));

        $sql = "CREATE TABLE {$this->wrap($this->table)} (\n"
            . implode(",\n", array_merge($columnSql, $tableIndexes))
            . "\n)";

        if ($this->driver === 'mysql') {
            $sql .= " ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        } else {
            $sql .= ";";
        }

        $result = $this->pdo->exec($sql) !== false;

        // Create regular indexes separately for PostgreSQL
        if ($this->driver === 'pgsql') {
            foreach ($this->indexes as $index) {
                if ($index->type === 'index') {
                    $indexName = $this->wrap($this->table . '_' . implode('_', $index->columns) . '_idx');
                    $cols = implode(", ", array_map(fn($c) => $this->wrap($c), $index->columns));
                    $sql = "CREATE INDEX $indexName ON {$this->wrap($this->table)} ($cols);";
                    $this->pdo->exec($sql);
                }
            }
        }

        return $result;
    }
}