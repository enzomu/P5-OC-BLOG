<?php

namespace Enzo\P5OcBlog\Services;

use Enzo\P5OcBlog\Controllers\UserController;

class AuthenticationService
{
    private UserController $userController;

    public function __construct(UserController $userController)
    {
        $this->userController = $userController;
    }

    public function authorize (array $requiredRoles): bool
    {
        if (!$this->userController->isUserLoggedIn()) {
            return false;
        }

        $userId = $_SESSION['user_id'];
        $userRoles = $this->userController->getUserRoles($userId);

        return count(array_intersect($requiredRoles, $userRoles)) > 0;
    }

    public function getRoles (): ?array
    {
        if (!$this->userController->isUserLoggedIn()) {
            return null;
        }
        $userId = $_SESSION['user_id'];
        return $this->userController->getUserRoles($userId);
    }
}