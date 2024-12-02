<?php

namespace Enzo\P5OcBlog\Controllers;

use Enzo\P5OcBlog\Repository\UserRepository;
use Enzo\P5OcBlog\Services\UserService;
use Symfony\Component\HttpFoundation\Session\Session;
use Twig\Environment;

class UserController
{
    private UserService $userService;
    private UserRepository $userRepository;
    private Environment $twig;
    private Session $session;

    public function __construct(UserService $userService, UserRepository $userRepository, Environment $twig, Session $session)
    {
        $this->userService = $userService;
        $this->userRepository = $userRepository;
        $this->twig = $twig;
        $this->session = $session;
    }

    public function register(): void
    {
        $error = null;
        $username = '';
        $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($password !== $confirm_password) {
                $error = "Les mots de passe ne correspondent pas.";
            } else {
                $result = $this->userService->register($username, $email, $password);

                if ($result['success']) {
                    header('Location: /index.php?page=login');
                    exit();
                } else {
                    $error = $result['message'];
                }
            }
        }

        echo $this->twig->render('register.html.twig', [
            'error' => $error,
            'username' => $username,
            'email' => $email
        ]);
    }

    public function login(): void
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = $this->userService->login($email, $password);

            if ($user) {
                $this->session->set('user_id', $user->getId());
                $this->session->set('username', $user->getUsername());
                $this->session->set('roles', $this->getUserRoles($user->getId()));
                header('Location: /index.php?page=home');
                exit();
            } else {
                $error = "Identifiants invalides";
            }
        }

        echo $this->twig->render('login.html.twig', [
            'error' => $error
        ]);
    }

    public function logout(): void
    {
        $this->session->clear();
        header('Location: /index.php?page=home');
        exit();
    }

    public function isUserLoggedIn(): bool
    {
        return $this->session->has('user_id');
    }

    public function getUserInfo(): array
    {
        if ($this->session->has('user_id')) {
            return [
                'isLoggedIn' => true,
                'username' => $this->session->get('username'),
                'roles' => $this->session->get('roles')
            ];
        }

        return [
            'isLoggedIn' => false,
            'username' => null,
            'role' => null
        ];
    }

    public function getUserRoles(int $userId): array
    {
        return $this->userRepository->getUserRoles($userId);
    }
}
