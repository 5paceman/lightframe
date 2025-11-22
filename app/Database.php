<?php

class DB {
    
    protected ?PDO $pdo = null;

    protected static ?DB $_instance = null;

    public function __construct() {}

    public static function get()
    {
        if(self::$_instance === null)
        {
            self::$_instance = new DB();
        }

        return self::$_instance;
    }

    public function connect()
    {
        if(!isset(Config::database['db'], Config::database['host'], Config::database['port'], Config::database['username'], Config::database['password'], Config::database['pdo']))
        {
            throw new Exception(message: "Config undefined for database");
        }
        
        switch(Config::database['pdo'])
        {
            case PDOTYPE::MYSQL:
                $dsn = "mysql:";
                break;
            case PDOTYPE::POSTGRES:
                $dsn = "pgsql:";
                break;
            default:
                throw new Exception("Unsupported driver");
        }
        
        $dsn .= "host=".Config::database['host'].";port=".Config::database['port'].";dbname=".Config::database['db'];
        $this->pdo = new PDO($dsn, Config::database['username'], Config::database['password'], Config::database['options']);
        return $this->pdo;
    }

    public function query()
    {
        $this->connect();
        return new QueryBuilder($this->pdo);
    }
}

class QueryBuilder {

    protected PDO $pdo;
    protected String $table;

    protected array $select = ['*'];
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $updates = [];
    protected array $insert = [];

    protected ?string $orderBy = null;
    protected ?string $limit = null;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function table($name)
    {
        $this->table = $name;
        return $this;
    }

    public function select(...$columns)
    {
        if($columns) {
            $this->select = $columns;
        }

        return $this;
    }

    public function where(string $column, string $operator = '=', $value)
    {
        $placeholder = ":".preg_replace('/[^a-z0-9_]/i', '_', $column).count($this->bindings);

        $this->wheres[] = "$column $operator $placeholder";
        $this->bindings[$placeholder] = $value;

        return $this;
    }

    public function orderBy(string $column, string $direction = "ASC")
    {
        $this->orderBy = "$column $direction";
        return $this;
    }

    public function limit(int $limit)
    {
        $this->limit = (string) $limit;
        return $this;
    }

    public function get()
    {
        $sql = "SELECT " . implode(", ", $this->select) . " FROM {$this->table}";
        
        if ($this->wheres) {
            $sql .= " WHERE " . implode(" AND ", $this->wheres);
        }

        if ($this->orderBy) {
            $sql .= " ORDER BY {$this->orderBy}";
        }

        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(): ?array
    {
        $this->limit(1);
        $result = $this->get();
        return $result[0] ?? null;
    }

    public function insert(array $data)
    {
        $this->insert = $data;

        $columns = array_keys($data);
        $placeholders = array_map(fn ($col) => ":".$col, $columns);

        $sql = "INSERT INTO {$this->table} (".implode(",", $columns).") VALUES (".implode(",", $placeholders).")";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($data);
    }

    public function update(array $data): bool
    {
        $this->updateData = $data;

        $setParts = [];
        foreach ($data as $col => $value) {
            $placeholder = ":" . $col;
            $setParts[] = "$col = $placeholder";
            $this->bindings[$placeholder] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(", ", $setParts);

        if ($this->wheres) {
            $sql .= " WHERE " . implode(" AND ", $this->wheres);
        }

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($this->bindings);
    }
}


?>