<?php

namespace Enzo\P5OcBlog\Controllers;

use Enzo\P5OcBlog\Services\AuthenticationService;
use Enzo\P5OcBlog\Services\CommentService;
use Enzo\P5OcBlog\Services\PostService;
use Twig\Environment;

class PostController
{
    private PostService $postService;
    private Environment $twig;
    private UserController $userController;
    private CommentService $commentService;

    private AuthenticationService $authenticationService;

    public function __construct(
        PostService $postService,
        CommentService $commentService,
        Environment $twig,
        UserController $userController,
        AuthenticationService $authenticationService
    )
    {
        $this->postService = $postService;
        $this->commentService = $commentService;
        $this->userController = $userController;
        $this->twig = $twig;
        $this->authenticationService = $authenticationService;
    }

    private function getGlobalParams(array $additionalParams = []): array
    {
        $userInfo = $this->userController->getUserInfo();
        return array_merge($userInfo, $additionalParams);
    }

    public function create(): void
    {
        if (!$this->authenticationService->authorize(['super_admin', 'admin'])) {
            header('Location: /index.php?page=home');
            exit;
        }
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
        if (!$this->authenticationService->authorize(['super_admin', 'admin'])) {
            header('Location: /index.php?page=home');
            exit;
        }
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
        if (!$this->authenticationService->authorize(['super_admin', 'admin'])) {
            header('Location: /index.php?page=home');
            exit;
        }
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

        $userRole = $this->authenticationService->getRoles();
        $userRole = is_array($userRole) ? $userRole : [];

        $onlyValidated = !in_array('admin', $userRole) && !in_array('super_admin', $userRole);

        $comments = $this->commentService->getCommentsWithUsernamesByPostId($postId, $onlyValidated);

        if (isset($_GET['action']) && isset($_GET['comment_id'])) {
            $commentId = (int) $_GET['comment_id'];
            $action = $_GET['action'];

            if ($this->authenticationService->authorize(['admin', 'super_admin'])) {
                if ($action === 'validate_comment') {
                    $this->commentService->validateComment($commentId);
                } elseif ($action === 'delete_comment') {
                    $this->commentService->deleteComment($commentId);
                }
                header("Location: /index.php?page=post&id={$postId}");
                exit;
            }
        }

        $params = $this->getGlobalParams(['post' => $post, 'comments' => $comments, 'isAdmin' => in_array('admin', $userRole) || in_array('super_admin', $userRole)]);
        echo $this->twig->render('post.html.twig', $params);
    }

/*    public function list(): void
    {
        $posts = $this->postService->findAllPosts();
        $params = $this->getGlobalParams(['posts' => $posts]);
        echo $this->twig->render('blog_list.html.twig', $params);
    }*/
    public function list(): void
    {
        $isAdmin = $this->authenticationService->authorize(['admin', 'super_admin']);
        $posts = $this->postService->findAllPosts();

        $postsWithCommentsInfo = [];

        foreach ($posts as $post) {
            $postId = $post->getId();

            $postData = [
                'post' => $post,
                'hasUnvalidatedComments' => false,
            ];

            if ($isAdmin) {
                $postData['hasUnvalidatedComments'] = $this->commentService->hasUnvalidatedComments($postId);
            }

            $postsWithCommentsInfo[] = $postData;
        }

        $params = $this->getGlobalParams(['posts' => $postsWithCommentsInfo, 'isAdmin' => $isAdmin]);
        echo $this->twig->render('blog_list.html.twig', $params);
    }
}