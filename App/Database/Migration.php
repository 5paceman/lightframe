<?php

namespace App\Database;

require 'vendor/autoload.php';

use App\Database\Database;
use App\Database\TableBuilder;

abstract class Migration {
    public TableBuilder $table;

    public function __construct(\PDO $pdo) {
        $this->table = new TableBuilder($pdo);
    }
    public abstract function migrate();
}

function migrate()
{
    $db = Database::get();
    $pdo = $db->connect();

    // Get all migration files and sort alphabetically (01_, 02_, etc.)
    $files = glob('Migrations/*.php');
    sort($files, SORT_STRING);

    foreach ($files as $file) {
        require_once $file;

        // Get filename without extension
        $filename = pathinfo($file, PATHINFO_FILENAME);

        // Remove numeric prefix (01_, 02_, etc.)
        $className = preg_replace('/^\d+_/', '', $filename);

        if (!class_exists($className)) {
            throw new \Exception("Class $className not found in $file");
        }

        $migration = new $className($pdo);

        if (!method_exists($migration, 'migrate')) {
            throw new \Exception("Class $className doesn't have migrate() method");
        }

        echo "Migrating $className...\n";
        $migration->migrate();
        $migration->table->create();
    }

    echo "All migrations complete.\n";
}

function drop()
{
    $db = Database::get();
    $pdo = $db->connect();

    // Get all migration files and sort alphabetically (01_, 02_, etc.)
    $files = glob('Migrations/*.php');
    rsort($files, SORT_STRING);

    foreach ($files as $file) {
        require_once $file;

        // Get filename without extension
        $filename = pathinfo($file, PATHINFO_FILENAME);

        // Remove numeric prefix (01_, 02_, etc.)
        $className = preg_replace('/^\d+_/', '', $filename);

        if (!class_exists($className)) {
            throw new \Exception("Class $className not found in $file");
        }

        $migration = new $className($pdo);

        if (!method_exists($migration, 'migrate')) {
            throw new \Exception("Class $className doesn't have migrate() method");
        }

        echo "Dropping $className...\n";
        $migration->migrate();
        $migration->table->drop();
    }

    echo "All drops complete.\n";
}

if(!isset($argv[1]))
{
    throw new \Exception("No migration command specified");
}

switch ($argv[1])
{
    case "migrate":
        migrate();
        break;
    case "drop":
        drop();
        break;
    default:
        echo "Unknown migration command";
}

?>