<?php

namespace Enzo\P5OcBlog\Controllers;

use Enzo\P5OcBlog\Services\AuthenticationService;
use Enzo\P5OcBlog\Services\CommentService;
use Enzo\P5OcBlog\Services\PostService;
use Enzo\P5OcBlog\Services\UserService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Twig\Environment;

class PostController
{
    private PostService $postService;
    private Environment $twig;
    private UserController $userController;
    private CommentService $commentService;
    private AuthenticationService $authenticationService;
    private UserService $userService;
    private Session $session;

    public function __construct(
        PostService $postService,
        CommentService $commentService,
        Environment $twig,
        UserController $userController,
        UserService $userService,
        AuthenticationService $authenticationService,
        Session $session
    ) {
        $this->postService = $postService;
        $this->commentService = $commentService;
        $this->userController = $userController;
        $this->userService = $userService;
        $this->twig = $twig;
        $this->authenticationService = $authenticationService;
        $this->session = $session;
    }

    public function getGlobalParams(array $additionalParams = []): array
    {
        $userInfo = $this->userController->getUserInfo();
        $sessionParams = [
            'isLoggedIn' => $this->session->has('user_id'),
            'username' => $this->session->get('username', ''),
        ];
        $isAdmin = $this->authenticationService->authorize(['super_admin', 'admin']);
        $sessionParams['isAdmin'] = $isAdmin;
        return array_merge($userInfo, $sessionParams, $additionalParams);
    }

    private function checkAdminAccess(): bool
    {
        if (!$this->authenticationService->authorize(['super_admin', 'admin'])) {
            $this->redirect('/index.php?page=home');
            return false;
        }
        return true;
    }

    private function redirect(string $url): void
    {
        $response = new RedirectResponse($url);
        $response->send();
    }

    private function renderEditPostPage(array $params): void
    {
        echo $this->twig->render('edit_post.html.twig', $params);
    }

    public function create(Request $request): void
    {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if ($request->getMethod() === 'POST') {
            $this->postCreation($request);
        } else {
            $params = $this->getGlobalParams();
            $this->renderEditPostPage($params);
        }
    }

    private function postCreation(Request $request): void
    {
        $postData = $this->getPostData($request);
        $result = $this->postService->createPost(
            $postData['title'],
            $postData['content'],
            $postData['image'],
            $postData['caption'],
            $postData['chapo'],
            $postData['userId']
        );

        if ($result['success']) {
            $this->redirect('/index.php?page=blog_list');
        } else {
            $params = $this->getGlobalParams(['error' => $result['message']]);
            $this->renderEditPostPage($params);
        }
    }

    private function getPostData(Request $request): array
    {
        return [
            'title' => $request->request->get('title', ''),
            'content' => $request->request->get('content', ''),
            'image' => $request->request->get('image', ''),
            'caption' => $request->request->get('caption', ''),
            'chapo' => $request->request->get('chapo', ''),
            'userId' => $this->session->get('user_id', null)
        ];
    }

    public function update(Request $request): void
    {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if ($request->getMethod() === 'POST') {
            $this->postUpdate($request);
        }
    }

    private function postUpdate(Request $request): void
    {
        $postId = $request->request->get('id', null);
        $updateData = [
            'title' => $request->request->get('title', ''),
            'content' => $request->request->get('content', ''),
            'image' => $request->request->get('image', ''),
            'caption' => $request->request->get('caption', ''),
            'chapo' => $request->request->get('chapo', '')
        ];

        $result = $this->postService->updatePost(
            $postId,
            $updateData['title'],
            $updateData['content'],
            $updateData['image'],
            $updateData['caption'],
            $updateData['chapo']
        );

        if ($result['success']) {
            $this->redirect('/index.php?page=blog_list');
        } else {
            $params = $this->getGlobalParams(['error' => $result['message']]);
            $this->renderEditPostPage($params);
        }
    }

    public function delete(Request $request, int $postId): void
    {
        if (!$this->checkAdminAccess()) {
            return;
        }
        $this->postService->deletePost($postId);
        $this->redirect('/index.php?page=blog_list');
    }

    public function show(int $postId): void
    {
        $post = $this->postService->findPostById($postId);

        if ($post === null) {
            echo $this->twig->render('404.html.twig');
            return;
        }

        $postData = $this->preparePostData($post, $postId);

        $params = $this->getGlobalParams($postData);
        echo $this->twig->render('post.html.twig', $params);
    }

    private function preparePostData($post, int $postId): array
    {
        $postAuthor = $this->userService->getUserById($post->getUserId());
        $userRole = $this->authenticationService->getRoles() ?? [];
        $isAdmin = in_array('admin', $userRole) || in_array('super_admin', $userRole);

        $onlyValidated = !$isAdmin;
        $comments = $this->commentService->getCommentsWithUsernamesByPostId($postId, $onlyValidated);

        return [
            'post' => $post,
            'postAuthor' => $postAuthor,
            'comments' => $comments,
            'isAdmin' => $isAdmin
        ];
    }

    public function list(): void
    {
        $isAdmin = $this->authenticationService->authorize(['admin', 'super_admin']);
        $posts = $this->postService->findAllPosts();

        $postsWithCommentsInfo = $this->postsWithCommentInfo($posts, $isAdmin);

        $params = $this->getGlobalParams(['posts' => $postsWithCommentsInfo, 'isAdmin' => $isAdmin]);
        echo $this->twig->render('blog_list.html.twig', $params);
    }

    private function postsWithCommentInfo(array $posts, bool $isAdmin): array
    {
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

        return $postsWithCommentsInfo;
    }
}
