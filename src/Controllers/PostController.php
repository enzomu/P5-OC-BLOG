<?php

namespace Enzo\P5OcBlog\Controllers;

use Enzo\P5OcBlog\Services\CommentService;
use Enzo\P5OcBlog\Services\PostService;
use Twig\Environment;

class PostController
{
    private PostService $postService;
    private Environment $twig;
    private UserController $userController;
    private CommentService $commentService;

    public function __construct(PostService $postService, CommentService $commentService, Environment $twig, UserController $userController)
    {
        $this->postService = $postService;
        $this->commentService = $commentService;
        $this->userController = $userController;
        $this->twig = $twig;
    }

    private function getGlobalParams(array $additionalParams = []): array
    {
        $userInfo = $this->userController->getUserInfo();
        return array_merge($userInfo, $additionalParams);
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $image = $_POST['image'] ?? '';
            $caption = $_POST['caption'] ?? '';
            $extraContent = $_POST['extra_content'] ?? '';
            $userId = $_SESSION['user_id'];

            $result = $this->postService->createPost($title, $content, $image, $caption, $extraContent, $userId);

            if ($result['success']) {
                header('Location: /index.php?page=blog_list');
                exit;
            } else {
                $params = $this->getGlobalParams(['error' => $result['message']]);
                echo $this->twig->render('edit_post.html.twig', $params);
            }
        } else {
            $params = $this->getGlobalParams();
            echo $this->twig->render('edit_post.html.twig', $params);
        }
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postId = $_POST['id'] ?? null;
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $image = $_POST['image'] ?? '';
            $caption = $_POST['caption'] ?? '';
            $extraContent = $_POST['extra_content'] ?? '';

            $result = $this->postService->updatePost($postId, $title, $content, $image, $caption, $extraContent);

            if ($result['success']) {
                header('Location: /index.php?page=blog_list');
                exit;
            } else {
                $params = $this->getGlobalParams(['error' => $result['message']]);
                echo $this->twig->render('edit_post.html.twig', $params);
            }
        }
    }

    public function delete(int $postId): void
    {
        $this->postService->deletePost($postId);
        header('Location: /index.php?page=blog_list');
        exit;
    }

/*    public function show(int $postId): void
    {
        $post = $this->postService->findPostById($postId);
        $comments = $this->commentService->getCommentsWithUsernamesByPostId($postId);
        dd($comments);
        $params = $this->getGlobalParams(['post' => $post, 'comments' => $comments]);
        echo $this->twig->render('post.html.twig', $params);
    }*/

    public function show(int $postId): void
    {
        $post = $this->postService->findPostById($postId);

        if ($post === null) {
            echo $this->twig->render('404.html.twig');
            return;
        }

        $comments = $this->commentService->getCommentsWithUsernamesByPostId($postId);
        $params = $this->getGlobalParams(['post' => $post, 'comments' => $comments]);
        echo $this->twig->render('post.html.twig', $params);
    }

    public function list(): void
    {
        $posts = $this->postService->findAllPosts();
        $params = $this->getGlobalParams(['posts' => $posts]);
        echo $this->twig->render('blog_list.html.twig', $params);
    }
}