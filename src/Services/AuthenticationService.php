<?php

namespace Enzo\P5OcBlog\Services;

use Enzo\P5OcBlog\Controllers\UserController;
use Symfony\Component\HttpFoundation\Session\Session;

class AuthenticationService
{
    private UserController $userController;
    private Session $session;

    public function __construct(UserController $userController, Session $session)
    {
        $this->userController = $userController;
        $this->session = $session;
    }

    public function authorize(array $requiredRoles): bool
    {
        if (!$this->userController->isUserLoggedIn()) {
            return false;
        }

        $userId = $this->session->get('user_id');

        if (!$userId) {
            return false;
        }

        $userRoles = $this->session->get('roles', []);

        return count(array_intersect($requiredRoles, $userRoles)) > 0;
    }

    public function getRoles(): ?array
    {
        if (!$this->userController->isUserLoggedIn()) {
            return null;
        }


        return $this->session->get('roles');
    }
}
