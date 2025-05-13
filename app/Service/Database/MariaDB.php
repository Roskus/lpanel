<?php declare(strict_types=1);

namespace App\Service\Database;

use PDO;
use PDOException;

class MariaDB
{
    protected PDO $connection;

    public function __construct()
    {
        $this->connect();
    }

    protected function connect(): void
    {
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');

        try {
            $this->connection = new PDO("mysql:host=$host;port=$port", $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new \RuntimeException('Could not connect to MariaDB: ' . $e->getMessage());
        }
    }

    public function listDatabases(): array
    {
        try {
            $query = $this->connection->query('SHOW DATABASES');
            $databases = $query->fetchAll(PDO::FETCH_COLUMN);

            // Ignorar bases de datos del sistema
            $systemDatabases = ['mysql', 'information_schema', 'performance_schema', 'sys'];
            return array_filter($databases, fn($db) => !in_array($db, $systemDatabases));
        } catch (PDOException $e) {
            throw new \RuntimeException('Error listing databases: ' . $e->getMessage());
        }
    }
}
