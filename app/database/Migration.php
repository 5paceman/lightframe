<?php

require 'Config.php';
require 'Database.php';
require 'TableBuilder.php';

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