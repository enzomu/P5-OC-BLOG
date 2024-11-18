<?php

namespace Enzo\P5OcBlog\Repository;

use Enzo\P5OcBlog\Entity\User;
use Enzo\P5OcBlog\Services\DbManager;
use PDO;

class UserRepository extends DbManager
{
    public function findById($id): ?User
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return $this->hydrate($data);
        }

        return null;
    }

    public function findByEmail($email): ?User
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return $this->hydrate($data);
        }

        return null;
    }

    private function hydrate(array $data): User
    {
        return new User(
            $data['id'],
            $data['username'],
            $data['email'],
            $data['password'],
            $data['role'],
            $data['created_at']
        );
    }

    public function save(User $user): bool
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, role, created_at) 
            VALUES (:username, :email, :password, :role, NOW())
        ");

        $stmt->bindValue(':username', $user->getUsername(), PDO::PARAM_STR);
        $stmt->bindValue(':email', $user->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(':password', $user->getPassword(), PDO::PARAM_STR);
        $stmt->bindValue(':role', $user->getRole(), PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function getUserRoles(int $userId): array
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare('SELECT role FROM users WHERE id = :user_id');
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
