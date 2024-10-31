<?php

namespace Enzo\P5OcBlog\Controllers;

use Enzo\P5OcBlog\Services\UserService;
use Twig\Environment;

class UserController
{
    private UserService $userService;
    private Environment $twig;

    public function __construct(UserService $userService, Environment $twig)
    {
        $this->userService = $userService;
        $this->twig = $twig;
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
                $_SESSION['user_id'] = $user->getId();
                header('Location: /index.php?page=home');
                exit();
            } else {
                $error = "Identifiants invalides";
            }
            echo $this->twig->render('login.html.twig', [
                'error' => $error
            ]);
        }
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        header('Location: /index.php?page=home');
        exit();
    }

    public function getUserInfo(): array
    {
        if (isset($_SESSION['user_id'])) {
            $user = $this->userService->getUserById($_SESSION['user_id']);
            return [
                'isLoggedIn' => true,
                'username' => $user->getUsername()
            ];
        }
        return ['isLoggedIn' => false, 'username' => null];
    }
}
