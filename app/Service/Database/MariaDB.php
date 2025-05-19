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

    public function createDatabase(string $name): void
    {
        try {
            $query = $this->connection->prepare("CREATE DATABASE IF NOT EXISTS `$name`");
            $query->execute();
        } catch (PDOException $e) {
            throw new \RuntimeException('Error creating database: ' . $e->getMessage());
        }
    }

    public function deleteDatabase(string $name): void
    {
        try {
            $this->connection->exec("DROP DATABASE `$name`");
        } catch (PDOException $e) {
            throw new \RuntimeException('Error deleting database: ' . $e->getMessage());
        }
    }

    public function listUsers(): array
    {
        $query = $this->connection->query("SELECT User, Host FROM mysql.user WHERE User NOT IN ('mysql.session','mysql.sys','root')");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUser(string $username, string $host): ?array
    {
        $stmt = $this->connection->prepare("SELECT User as username, Host as host FROM mysql.user WHERE User = ? AND Host = ?");
        $stmt->execute([$username, $host]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createUser(string $username, string $host, string $password, string $privileges, ?string $database = null): void
    {
        $db = $database ? "`$database`.*" : "*.*";
        $this->connection->exec("CREATE USER '$username'@'$host' IDENTIFIED BY '$password'");
        $this->connection->exec("GRANT $privileges ON $db TO '$username'@'$host'");
        $this->connection->exec("FLUSH PRIVILEGES");
    }

    public function updateUser(string $username, string $host, ?string $password, string $privileges, ?string $database = null): void
    {
        $db = $database ? "`$database`.*" : "*.*";
        if ($password) {
            $this->connection->exec("ALTER USER '$username'@'$host' IDENTIFIED BY '$password'");
        }
        $this->connection->exec("REVOKE ALL PRIVILEGES, GRANT OPTION FROM '$username'@'$host'");
        $this->connection->exec("GRANT $privileges ON $db TO '$username'@'$host'");
        $this->connection->exec("FLUSH PRIVILEGES");
    }

    public function deleteUser(string $username, string $host): void
    {
        $this->connection->exec("DROP USER '$username'@'$host'");
        $this->connection->exec("FLUSH PRIVILEGES");
    }
}

