<?php

namespace Enzo\P5OcBlog\Controllers;

use Enzo\P5OcBlog\Services\AuthenticationService;
use Enzo\P5OcBlog\Services\CommentService;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class CommentController
{
    private AuthenticationService $authenticationService;
    private CommentService $commentService;
    private Environment $twig;
    private Session $session;

    public function __construct(
        CommentService $commentService,
        Environment $twig,
        AuthenticationService $authenticationService,
        Session $session
    ) {
        $this->commentService = $commentService;
        $this->twig = $twig;
        $this->authenticationService = $authenticationService;
        $this->session = $session;
    }

    public function create(int $postId, Request $request): void
    {
        if (!$this->authenticationService->authorize(['super_admin', 'admin', 'registered_user'])) {
            $this->session->getFlashBag()->add('error', 'Log in to comment');
            header("Location: /index.php?page=post&id=$postId");
            exit;
        }

        if ($request->isMethod('POST')) {
            $content = trim($request->request->get('content', ''));
            $userId = (int) $this->session->get('user_id');
            $roles = $this->authenticationService->getRoles();
            $validated = in_array('registered_user', $roles) ? 0 : 1;

            $result = $this->commentService->createComment($content, $userId, $postId, $validated);

            if ($result['success']) {
                $this->session->getFlashBag()->add('success', $result['message']);
            } else {
                $this->session->getFlashBag()->add('error', $result['message']);
            }

            header("Location: /index.php?page=post&id=$postId");
            exit;
        }
    }

    public function update(int $commentId, Request $request): void
    {
        if (!$this->authenticationService->authorize(['super_admin', 'admin'])) {
            header('Location: /index.php?page=home');
            exit;
        }

        if ($request->isMethod('POST')) {
            $content = trim($request->request->get('content', ''));
            $result = $this->commentService->updateComment($commentId, $content);

            if ($result['success']) {
                $postId = $request->request->get('post_id');
                header("Location: /index.php?page=post&id=$postId");
                exit;
            } else {
                echo $this->twig->render('error.html.twig', ['message' => $result['message']]);
            }
        }
    }

    public function delete(int $commentId, Request $request): void
    {
        if (!$this->authenticationService->authorize(['super_admin', 'admin'])) {
            header('Location: /index.php?page=home');
            exit;
        }

        $result = $this->commentService->deleteComment($commentId);
        $postId = $request->query->get('post_id');

        if ($result['success']) {
            header("Location: /index.php?page=post&id=$postId");
            exit;
        } else {
            echo $this->twig->render('error.html.twig', ['message' => $result['message']]);
        }
    }
}