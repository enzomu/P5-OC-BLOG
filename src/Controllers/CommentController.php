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
       // $this->userController = $userController;
    }

    public function showComments(int $postId)
    {
        $comments = $this->commentService->getCommentsByPostId($postId);
        echo $this->twig->render('comments.html.twig', [
            'comments' => $comments,
            'postId' => $postId
        ]);
    }

    public function create(int $postId)
    {
        if (!$this->authenticationService->authorize(['super_admin', 'admin', 'registered_user'])) {
            header('Location: /index.php?page=home');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = $_POST['content'];
            $userId = (int) $_SESSION['user_id'];
            $validated = $this->authenticationService->getRoles()[0] === 'registered_user' ? 0 : 1;

            $result = $this->commentService->createComment($content, $userId, $postId, $validated);
            if ($result['success']) {
                header("Location: /index.php?page=post&id=$postId");
                exit();
            } else {
                echo $this->twig->render('error.html.twig', ['message' => $result['message']]);
            }
        }
    }

/*    public function getCommentsByPostId(int $postId): void
    {
        $comments = $this->commentService->getCommentsWithUsernamesByPostId($postId);
        echo $this->twig->render('comments.html.twig', [
            'comments' => $comments
        ]);
    }*/

    public function update(int $commentId)
    {
        if (!$this->authenticationService->authorize(['super_admin', 'admin'])) {
            header('Location: /index.php?page=home');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = $_POST['content'];


            $result = $this->commentService->updateComment($commentId, $content);
            if ($result['success']) {
                $postId = $_POST['post_id'];
                header("Location: /index.php?page=post&id=$postId");
                exit();
            } else {
                echo $this->twig->render('error.html.twig', ['message' => $result['message']]);
            }
        }
    }

    public function delete(int $commentId)
    {
        if (!$this->authenticationService->authorize(['super_admin', 'admin'])) {
            header('Location: /index.php?page=home');
            exit;
        }
        $result = $this->commentService->deleteComment($commentId);
        if ($result['success']) {
            $postId = $_GET['post_id'];
            header("Location: /index.php?page=post&id=$postId");
            exit();
        } else {
            echo $this->twig->render('error.html.twig', ['message' => $result['message']]);
        }
    }
}