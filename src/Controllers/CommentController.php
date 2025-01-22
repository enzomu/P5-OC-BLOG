<?php

namespace Enzo\P5OcBlog\Controllers;

use Enzo\P5OcBlog\Services\AuthenticationService;
use Enzo\P5OcBlog\Services\CommentService;
use Enzo\P5OcBlog\Services\RequestManager;
use Enzo\P5OcBlog\Services\SessionManager;
use Twig\Environment;

class CommentController
{
    private AuthenticationService $authenticationService;
    private CommentService $commentService;
    private Environment $twig;
    private RequestManager $requestManager;

    public function __construct(CommentService $commentService, Environment $twig, AuthenticationService $authenticationService, requestManager $requestManager)
    {
        $this->commentService = $commentService;
        $this->twig = $twig;
        $this->authenticationService = $authenticationService;
        $this->requestManager = $requestManager;
    }

    public function create(int $postId): string
    {
        if (!$this->authenticationService->authorize(['super_admin', 'admin', 'registered_user'])) {
            return $this->redirect("/index.php?page=post&id=$postId");
        }

        if ($this->requestManager->isPost()) {
            $content = $this->requestManager->getPost('content');
            $userId = (int) SessionManager::get('user_id');
            $validated = $this->authenticationService->getRoles()[0] === 'registered_user' ? 0 : 1;
            $result = $this->commentService->createComment($content, $userId, $postId, $validated);

            return $this->redirect("/index.php?page=post&id=$postId");
        }

        return $this->twig->render('404.html.twig');
    }

    public function update(int $commentId): string
    {
        if (!$this->authenticationService->authorize(['super_admin', 'admin'])) {
            return $this->redirect('/index.php?page=home');
        }

        if ($this->requestManager->isPost()) {
            $content = $this->requestManager->getPost('content');

            $result = $this->commentService->updateComment($commentId, $content);

            if ($result['success']) {
                $postId = $this->requestManager->getPost('post_id');
                return $this->redirect("/index.php?page=post&id=$postId");
            } else {
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
            $postId = $this->requestManager->getGet('post_id');
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
