<?php

use Enzo\P5OcBlog\Controllers\UserController;
use Enzo\P5OcBlog\Controllers\PostController;
use Enzo\P5OcBlog\Controllers\CommentController;
use Twig\Environment;

function handleRouting(string $page, array $params, UserController $userController, PostController $postController, CommentController $commentController, Environment $twig): void
{
    switch ($page) {
        case 'home':
            echo $twig->render('home.html.twig', $params);
            break;

        case 'login_user':
            $userController->login();
            break;

        case 'login':
            echo $twig->render('login.html.twig', $params);
            break;

        case 'logout':
            $userController->logout();
            break;

        case 'register_user':
            $userController->register();
            break;

        case 'register':
            echo $twig->render('register.html.twig', $params);
            break;

        case 'blog_list':
            $postController->list();
            break;

        case 'post':
            $postId = $_GET['id'] ?? null;
            if ($postId) {
                $postController->show((int)$postId);
            } else {
                echo $twig->render('404.html.twig');
            }
            break;

        case 'create_post':
            $postController->create();
            break;

        case 'update_post':
            $postController->update();
            break;

        case 'edit_post':
            $postId = $_GET['id'] ?? null;
            if ($postId) {
                $postController->showEditForm((int)$postId);
            } else {
                echo $twig->render('404.html.twig');
            }
            break;

        case 'delete_post':
            $postId = $_GET['id'] ?? null;
            if ($postId) {
                $postController->delete($postId);
            }
            break;

        case 'create_comment':
            $postId = $_GET['post_id'] ?? null;
            if ($postId) {
                $commentController->create($postId);
            }
            break;

        case 'update_comment':
            $commentId = $_GET['id'] ?? null;
            if ($commentId) {
                $commentController->update($commentId);
            }
            break;

        case 'delete_comment':
            $commentId = $_GET['id'] ?? null;
            if ($commentId) {
                $commentController->delete($commentId);
            }
            break;

        default:
            echo $twig->render('404.html.twig');
            break;
    }
}
