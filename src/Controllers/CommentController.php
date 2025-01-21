<?php

namespace Enzo\P5OcBlog\Controllers;

use Enzo\P5OcBlog\Services\AuthenticationService;
use Enzo\P5OcBlog\Services\CommentService;
use Twig\Environment;

class CommentController
{
    private AuthenticationService $authenticationService;
    private CommentService $commentService;
    private Environment $twig;
   // private UserController $userController;

    public function __construct(CommentService $commentService, Environment $twig, AuthenticationService $authenticationService, /*UserController $userController*/)
    {
        $this->commentService = $commentService;
        $this->twig = $twig;
        $this->authenticationService = $authenticationService;
    }


    public function create(int $postId): string
    {
        if (!$this->authenticationService->authorize(['super_admin', 'admin', 'registered_user'])) {
            $_SESSION['error_message'] = 'Log in to comment';
            return $this->redirect("/index.php?page=post&id=$postId");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = $_POST['content'];
            $userId = (int) $_SESSION['user_id'];
            $validated = $this->authenticationService->getRoles()[0] === 'registered_user' ? 0 : 1;
            $result = $this->commentService->createComment($content, $userId, $postId, $validated);

            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
            return $this->redirect("/index.php?page=post&id=$postId");
/*            unset($_SESSION['success_message']);
            unset($_SESSION['error_message']);*/
        }
        return $this->twig->render('404.html.twig');
    }


    public function update(int $commentId): string
    {
        if (!$this->authenticationService->authorize(['super_admin', 'admin'])) {
            $_SESSION['error_message'] = 'You are not authorized to edit comments.';
            return $this->redirect('/index.php?page=home');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = $_POST['content'];

            $result = $this->commentService->updateComment($commentId, $content);

            if ($result['success']) {
                $postId = $_POST['post_id'];
                $_SESSION['success_message'] = 'Comment updated successfully.';
                return $this->redirect("/index.php?page=post&id=$postId");
            } else {
                $_SESSION['error_message'] = $result['message'];
                return $this->redirect("/index.php?page=post&id={$result['post_id']}");
            }
        }

        return $this->twig->render('404.html.twig');
    }

    public function delete(int $commentId): string
    {
        if (!$this->authenticationService->authorize(['super_admin', 'admin'])) {
            return $this->redirect("/index.php?page=home");
        }
        $result = $this->commentService->deleteComment($commentId);
        if ($result['success']) {
            $postId = $_GET['post_id'];
            return $this->redirect("/index.php?page=post&id=$postId");
        } else {
            return $this->twig->render('error.html.twig', ['message' => $result['message']]);
        }
    }

    private function redirect(string $url): string
    {
        return json_encode(['redirect' => $url]);
    }
}