<?php

namespace Enzo\P5OcBlog\Controllers;

use Enzo\P5OcBlog\Services\CommentService;
use Twig\Environment;

class CommentController
{
    private CommentService $commentService;
    private Environment $twig;

    public function __construct(CommentService $commentService, Environment $twig)
    {
        $this->commentService = $commentService;
        $this->twig = $twig;
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = $_POST['content'];
            $userId = (int) $_SESSION['user_id'];

            $result = $this->commentService->createComment($content, $userId, $postId);
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