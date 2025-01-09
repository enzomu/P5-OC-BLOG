<?php

namespace Enzo\P5OcBlog\Controllers;

use Enzo\P5OcBlog\Services\AuthenticationService;
use Enzo\P5OcBlog\Services\CommentService;
use Enzo\P5OcBlog\Services\PostService;
use Enzo\P5OcBlog\Services\UserService;
use Twig\Environment;

class PostController
{
    private PostService $postService;
    private Environment $twig;
    private UserController $userController;
    private CommentService $commentService;

    private AuthenticationService $authenticationService;
    private UserService $userService;

    public function __construct(
        PostService $postService,
        CommentService $commentService,
        Environment $twig,
        UserController $userController,
        UserService $userService,
        AuthenticationService $authenticationService
    )
    {
        $this->postService = $postService;
        $this->commentService = $commentService;
        $this->userController = $userController;
        $this->userService = $userService;
        $this->twig = $twig;
        $this->authenticationService = $authenticationService;
    }

    private function getGlobalParams(array $additionalParams = []): array
    {
        $userInfo = $this->userController->getUserInfo();
        return array_merge($userInfo, $additionalParams);
    }

    public function create(): string
    {
        if (!$this->authenticationService->authorize(['super_admin', 'admin'])) {
            header('Location: /index.php?page=home');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = [
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
                'image' => $_POST['image'] ?? '',
                'caption' => $_POST['caption'] ?? '',
                'chapo' => $_POST['chapo'] ?? '',
            ];

            if (!$this->postService->validatePostData($data)) {
                header('Location: /index.php?page=create_post');
                exit;
            }


            $data['userId'] = $_SESSION['user_id'];

            $result = $this->postService->createPost(
                $data['title'],
                $data['content'],
                $data['image'],
                $data['caption'],
                $data['chapo'],
                $data['userId']
            );


            if ($result['success']) {
                return $this->redirect('/index.php?page=blog_list');
            } else {
                $params = $this->getGlobalParams(['error' => $result['message']]);
                return $this->twig->render('edit_post.html.twig', $params);
            }
        } else {
            $params = $this->getGlobalParams();
            return $this->twig->render('edit_post.html.twig', $params);
        }
    }

    public function update(): string
    {
        if (!$this->authenticationService->authorize(['super_admin', 'admin'])) {
            return $this->redirect('/index.php?page=home');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $_POST['id'] ?? null,
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
                'image' => $_POST['image'] ?? '',
                'caption' => $_POST['caption'] ?? '',
                'chapo' => $_POST['chapo'] ?? '',
            ];

            if (!$this->postService->validatePostData($data)) {
                return $this->redirect('/index.php?page=edit_post&id=' . ($data['id'] ?? ''));
            }

            $result = $this->postService->updatePost(
                $data['id'],
                $data['title'],
                $data['content'],
                $data['image'],
                $data['caption'],
                $data['chapo']
            );

            if ($result['success']) {
                return $this->redirect('/index.php?page=blog_list');
            }

            $params = $this->getGlobalParams(['error' => $result['message']]);
            return $this->twig->render('edit_post.html.twig', $params);
        }

        return $this->twig->render('404.html.twig');
    }


    public function showEditForm(int $postId): string
    {
        $post = $this->postService->findPostById($postId);

        if ($post === null) {
            return $this->twig->render('404.html.twig');
        }

        $params = $this->getGlobalParams(['post' => $post]);
        return $this->twig->render('edit_post.html.twig', $params);
    }

    public function delete(int $postId): string
    {
        if (!$this->authenticationService->authorize(['super_admin', 'admin'])) {
            return $this->redirect('/index.php?page=home');
        }
        $this->postService->deletePost($postId);
        header('Location: /index.php?page=blog_list');
        exit;
    }

    public function show(int $postId): string
    {
        $post = $this->postService->findPostById($postId);

        if ($post === null) {
            return $this->twig->render('404.html.twig');
        }

        $postAuthor = $this->userService->getUserById($post->getUserId());
        $userRole = $this->authenticationService->getRoles();
        $userRole = is_array($userRole) ? $userRole : [];

        $onlyValidated = !in_array('admin', $userRole) && !in_array('super_admin', $userRole);
        $comments = $this->commentService->getCommentsWithUsernamesByPostId($postId, $onlyValidated);

        if ($this->handleAction($postId)) {
            return $this->redirect("/index.php?page=post&id={$postId}");
        }

        $params = $this->getGlobalParams([
            'post' => $post,
            'postAuthor' => $postAuthor,
            'comments' => $comments,
            'isAdmin' => in_array('admin', $userRole) || in_array('super_admin', $userRole)
        ]);

        return $this->twig->render('post.html.twig', $params);
    }

    private function handleAction(int $postId): bool
    {
        if (!isset($_GET['action'])) {
            return false;
        }

        $action = $_GET['action'];

        if ($action === 'delete_post' && $this->authenticationService->authorize(['admin', 'super_admin'])) {
            $this->postService->deletePost($postId);
            return true;
        }

        if (isset($_GET['comment_id'])) {
            $commentId = (int) $_GET['comment_id'];

            if ($this->authenticationService->authorize(['admin', 'super_admin'])) {
                if ($action === 'validate_comment') {
                    $this->commentService->validateComment($commentId);
                } elseif ($action === 'delete_comment') {
                    $this->commentService->deleteComment($commentId);
                }
            }
        }

        return false;
    }

    public function list(): string
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
        return $this->twig->render('blog_list.html.twig', $params);
    }

    private function redirect(string $url): string
    {
        return json_encode(['redirect' => $url]);
    }
}