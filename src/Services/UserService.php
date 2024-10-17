<?php

namespace Enzo\P5OcBlog\Services;

use Enzo\P5OcBlog\Entity\User;
use Enzo\P5OcBlog\Repository\UserRepository;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register($username, $email, $password, $role = 'registered_user'): array
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $user = new User(null, $username, $email, $hashedPassword, $role, null);

        if ($this->userRepository->save($user)) {
            return ['success' => true, 'message' => 'Inscription réussie'];
        } else {
            return ['success' => false, 'message' => "Erreur lors de l'enregistrement"];
        }
    }

    public function login($email, $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);

        if ($user && /*password_verify($password, $user->getPassword()))*/$password=== $user->getPassword()) {
            return $user;
        }

        return null;
    }
    public function getUserById($userId): ?User
    {
        return $this->userRepository->findById($userId);
    }
}
