<?php

namespace Enzo\P5OcBlog\Services;

use Dotenv\Dotenv;
use PDO;
use PDOException;

class DbManager
{
    private string $host;
    private string $db;
    private string $user;
    private string $pass;
    private PDO $pdo;

    public function __construct()
    {
        $envPath = realpath(__DIR__ . '/../../.env');
        $dotenv = Dotenv::createImmutable(dirname($envPath), '.env');
        $dotenv->load();

        $this->host = $this->getEnvValue('DB_HOST');
        $this->db = $this->getEnvValue('DB_NAME');
        $this->user = $this->getEnvValue('DB_USER');
        $this->pass = $this->getEnvValue('DB_PASS');

        try {
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->db};charset=utf8",
                $this->user,
                $this->pass
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new PDOException('Could not connect to the database.');
        }
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    private function getEnvValue(string $key): string
    {
        if (!isset($_ENV[$key])) {
            throw new \RuntimeException("Environment variable '{$key}' is not set.");
        }
        return $_ENV[$key];
    }
}
