<?php

namespace Enzo\P5OcBlog\Controllers;

use Enzo\P5OcBlog\Repository\UserRepository;
use Enzo\P5OcBlog\Services\RequestManager;
use Enzo\P5OcBlog\Services\SessionManager;
use Enzo\P5OcBlog\Services\UserService;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Twig\Environment;

class UserController
{
    private UserService $userService;
    private Environment $twig;
    private UserRepository $userRepository;
    private RequestManager $requestManager;

    public function __construct(UserService $userService, Environment $twig, UserRepository $userRepository, RequestManager $requestManager)
    {
        $this->userService = $userService;
        $this->twig = $twig;
        $this->userRepository = $userRepository;
        $this->requestManager = $requestManager;
    }

    public function register(): string
    {
        $error = null;
        $username = '';
        $email = '';

        if ($this->requestManager->isPost()) {
            $username = $this->requestManager->getPost('username');
            $email = $this->requestManager->getPost('email');
            $password = $this->requestManager->getPost('password');
            $confirmPassword = $this->requestManager->getPost('confirm_password');

            if ($password !== $confirmPassword) {
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
            'username' => addslashes($username),
            'email' => $email
        ]);
    }

    public function login(): string
    {
        if ($this->requestManager->isPost()) {
            $email = $this->requestManager->getPost('email');
            $password = $this->requestManager->getPost('password');

            $user = $this->userService->login($email, $password);

            if ($user) {
                SessionManager::set('user_id', $user->getId());
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
        SessionManager::clear();
        return $this->redirect("/index.php?page=home");
    }

    public function isUserLoggedIn(): bool
    {
        return !empty(SessionManager::get('user_id'));
    }

    public function getUserInfo(): array
    {
        $userId = SessionManager::get('user_id');

        if ($userId) {
            $user = $this->userService->getUserById($userId);
            return [
                'isLoggedIn' => true,
                'username' => $user->getUsername(),
                'role' => $user->getRole()
            ];
        }

        return [
            'isLoggedIn' => false,
            'username' => null,
            'role' => null
        ];
    }

    public function sendEmail(): string
    {
        if ($this->requestManager->isPost()) {
            $name = $this->requestManager->getPost('name');
            $email = $this->requestManager->getPost('email');
            $message = $this->requestManager->getPost('message');

          if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($name) || empty($message)) {
                return $this->redirect("/index.php?page=home");
            }

            $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Debugoutput = function ($str, $level) {
                    error_log("SMTP Debug level $level: $str");
                };
                $mail->Host = 'smtp.sendgrid.net';
                $mail->SMTPAuth = true;
                $mail->Username = 'apikey';
                $mail->Password = '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';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('spirit_49@hotmail.fr', 'SendGrid');
                $mail->addAddress('spirit_49@hotmail.fr');
                $mail->addReplyTo($email, $name);
                $mail->Subject = 'New mail from form';
                $mail->Body = $message;

                $mail->send();
        }

        return $this->redirect("/index.php?page=home");
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
