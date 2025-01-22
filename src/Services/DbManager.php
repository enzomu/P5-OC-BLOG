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
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2), '.env');
        $dotenv->load();

        $this->host = $_ENV['DB_HOST'];
        $this->db = $_ENV['DB_NAME'];
        $this->user = $_ENV['DB_USER'];
        $this->pass = $_ENV['DB_PASS'];

        try {
            $this->pdo = new PDO("mysql:host={$this->host};dbname={$this->db};charset=utf8", $this->user, $this->pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection failed';
        }
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
