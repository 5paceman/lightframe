<?php

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