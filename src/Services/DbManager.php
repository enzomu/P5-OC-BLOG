<?php

namespace Enzo\P5OcBlog\Services;

use PDO;
use PDOException;

class DbManager
{
    private string $host = 'localhost';
    private string $db = 'blog_db';
    private string $user = 'root';
    private string $pass = 'root';
    private PDO $pdo;

    public function __construct()
    {
        try {
            $this->pdo = new PDO("mysql:host={$this->host};dbname={$this->db};charset=utf8", $this->user, $this->pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}