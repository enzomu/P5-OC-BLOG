<?php

namespace Enzo\P5OcBlog\Controllers;

use Enzo\P5OcBlog\Repository\UserRepository;
use Enzo\P5OcBlog\Services\UserService;
use Twig\Environment;

class UserController
{
    private UserService $userService;
    private Environment $twig;
    private $userRepository;

    public function __construct(UserService $userService, Environment $twig, UserRepository $userRepository)
    {
        $this->userService = $userService;
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function register(): string
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
                    return $this->redirect("/index.php?page=login");
                } else {
                    $error = $result['message'];
                }
            }
        }

        return $this->twig->render('register.html.twig', [
            'error' => $error,
            'username' => addslashes($username,),
            'email' => $email
        ]);
    }

    public function login(): string
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = $this->userService->login($email, $password);

            if ($user) {
                $_SESSION['user_id'] = $user->getId();
                return $this->redirect("/index.php?page=home");
            } else {
                $error = "Identifiants invalides";
            }
            return $this->twig->render('login.html.twig', [
                'error' => $error
            ]);
        }
        return $this->twig->render('404.html.twig');
    }

    public function logout(): string
    {
        session_unset();
        session_destroy();
        return $this->redirect("/index.php?page=home");
    }

    public function isUserLoggedIn(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public function getUserInfo(): array
    {
        if (isset($_SESSION['user_id'])) {
            $user = $this->userService->getUserById($_SESSION['user_id']);
            return [
                'isLoggedIn' => true,
                'username' => $user->getUsername(),
                'role' => $user->getRole()
            ];
        }
        return ['isLoggedIn' => false, 'username' => null, 'role' => null,];
    }

    public function getUserRoles(int $userId): array
    {
        return $this->userRepository->getUserRoles($userId);
    }

    private function redirect(string $url): string
    {
        return json_encode(['redirect' => $url]);
    }
}
