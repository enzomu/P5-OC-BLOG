<?php

use Enzo\P5OcBlog\Controllers\UserController;
use Enzo\P5OcBlog\Controllers\PostController;
use Enzo\P5OcBlog\Controllers\CommentController;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

function handleRouting(string $page, array $params, UserController $userController, PostController $postController, CommentController $commentController, Environment $twig): void
{
    $globalParams = $postController->getGlobalParams();
    $request = Request::createFromGlobals();
    switch ($page) {
        case 'home':
            echo $twig->render('home.html.twig',  array_merge($globalParams, $params));
            break;

        case 'login_user':
            $userController->login();
            break;

        case 'login':
            echo $twig->render('login.html.twig',  array_merge($globalParams, $params));
            break;

        case 'logout':
            $userController->logout();
            break;

        case 'register_user':
            $userController->register();
            break;

        case 'register':
            echo $twig->render('register.html.twig',  array_merge($globalParams, $params));
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
            $postController->create($request);
            break;

        case 'update_post':
            $postController->update($request);
            break;

        case 'edit_post':
            $postId = $_GET['id'] ?? null;
            if ($postId) {
                $postController->update($request);
            } else {
                echo $twig->render('404.html.twig');
            }
            break;

        case 'delete_post':
            $postId = $_GET['id'] ?? null;
            if ($postId) {
                $postController->delete($request, $postId);
            }
            break;

        case 'create_comment':
            $postId = $_GET['post_id'] ?? null;
            if ($postId) {
                $commentController->create($postId, $request);
            }
            break;

        case 'update_comment':
            $commentId = $_GET['id'] ?? null;
            if ($commentId) {
                $commentController->update($commentId, $request);
            }
            break;

        case 'delete_comment':
            $commentId = $_GET['id'] ?? null;
            if ($commentId) {
                $commentController->delete($commentId, $request);
            }
            break;

        default:
            echo $twig->render('404.html.twig');
            break;
    }
}
