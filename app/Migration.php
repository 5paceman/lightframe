<?php

require 'Config.php';
require 'Database.php';

class TableBuilder {
    protected PDO $pdo;
    protected string $table;
    protected array $columns = [];
    protected ?string $primaryKey = null;
    protected string $driver;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    public function name(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    // --------------------------
    // Column Definitions
    // --------------------------
    public function increments(string $name): static
    {
        if ($this->driver === 'pgsql') {
            $this->columns[] = "$name SERIAL";
        } else {
            $this->columns[] = "$name INT AUTO_INCREMENT";
        }

        $this->primaryKey = $name;
        return $this;
    }

    public function integer(string $name, bool $nullable = false): static
    {
        if ($this->driver === 'pgsql') {
            $type = "INTEGER";
        } else {
            $type = "INT";
        }

        $this->columns[] = "$name $type" . ($nullable ? " NULL" : " NOT NULL");
        return $this;
    }

    public function string(string $name, int $length = 255, bool $nullable = false): static
    {
        $this->columns[] = "$name VARCHAR($length)" . ($nullable ? " NULL" : " NOT NULL");
        return $this;
    }

    public function text(string $name, bool $nullable = false): static
    {
        $this->columns[] = "$name TEXT" . ($nullable ? " NULL" : " NOT NULL");
        return $this;
    }

    public function boolean(string $name, bool $nullable = false): static
    {
        $type = $this->driver === 'pgsql' ? "BOOLEAN" : "TINYINT(1)";
        $this->columns[] = "$name $type" . ($nullable ? " NULL" : " NOT NULL");
        return $this;
    }

    public function timestamp(string $name, bool $nullable = false, bool $defaultNow = false): static
    {
        $col = "$name TIMESTAMP";

        if ($defaultNow) {
            $col .= " DEFAULT CURRENT_TIMESTAMP";
        } elseif (!$nullable) {
            $col .= " NOT NULL";
        }

        $this->columns[] = $col;
        return $this;
    }

    // --------------------------
    // Create Table
    // --------------------------
    public function create(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (\n";
        $sql .= implode(",\n", $this->columns);

        if ($this->primaryKey) {
            $sql .= ",\nPRIMARY KEY ({$this->primaryKey})";
        }

        $sql .= "\n)";

        // MySQL-specific engine declaration
        if ($this->driver === 'mysql') {
            $sql .= " ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        } else {
            $sql .= ";";
        }

        return $this->pdo->exec($sql) !== false;
    }

    public function drop(): bool
    {
        $sql = "DROP TABLE IF EXISTS {$this->table}";
        return $this->pdo->exec($sql) !== false;
    }
}

abstract class Migration {
    public TableBuilder $table;

    public function __construct(PDO $pdo) {
        $this->table = new TableBuilder($pdo);
    }
    public abstract function migrate();
}

function migrate()
{
    $db = DB::get();
    $pdo = $db->connect();

    $files = glob('migrations/*.php');
    foreach($files as $file)
    {
        require_once $file;

        $className = pathinfo($file, PATHINFO_FILENAME);
        if(!class_exists($className))
        {
            throw new Exception("Class $className not found in $file");
        }

        $migration = new $className($pdo);

        if(!method_exists($migration, 'migrate')) 
        {
            throw new Exception("Class $className doesnt have migrate() func");
        }

        echo "Migrating $className...\n";
        $migration->migrate();
        $migration->table->create();
    }
    echo "All migrations complete.\n";
}

if(!isset($argv[1]))
{
    throw new Exception("No migration command specified");
}

switch ($argv[1])
{
    case "migrate":
        migrate();
        break;
    default:
        echo "Unknown migration command";
}

?>