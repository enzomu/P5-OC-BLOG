<?php

use Enzo\P5OcBlog\Controllers\UserController;
use Enzo\P5OcBlog\Controllers\PostController;
use Enzo\P5OcBlog\Controllers\CommentController;
use Twig\Environment;

function handleRouting(
    string $page,
    array $params,
    UserController $userController,
    PostController $postController,
    CommentController $commentController,
    Environment $twig
): string {
    switch ($page) {
        case 'home':
            return $twig->render('home.html.twig', $params);

        case 'login_user':
            return $userController->login();

        case 'login':
            return $twig->render('login.html.twig', $params);

        case 'logout':
            return $userController->logout();

        case 'register_user':
            return $userController->register();

        case 'register':
            return $twig->render('register.html.twig', $params);

        case 'blog_list':
            return $postController->list();

        case 'post':
            $postId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if ($postId) {
                return $postController->show($postId);
            }
            return $twig->render('404.html.twig');

        case 'create_post':
            return $postController->create();

        case 'update_post':
            return $postController->update();

        case 'edit_post':
            $postId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if ($postId) {
                return $postController->showEditForm($postId);
            }
            return $twig->render('404.html.twig');

        case 'delete_post':
            $postId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if ($postId) {
                return $postController->delete($postId);
            }
            return $twig->render('blog_list.html.twig');

        case 'create_comment':
            $postId = filter_input(INPUT_GET, 'post_id', FILTER_VALIDATE_INT);
            if ($postId) {
                return $commentController->create($postId);
            }
            return $twig->render('404.html.twig');

        case 'update_comment':
            $commentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if ($commentId) {
                return $commentController->update($commentId);
            }
            return $twig->render('404.html.twig');

        case 'delete_comment':
            $commentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if ($commentId) {
                return $commentController->delete($commentId);
            }
            return $twig->render('404.html.twig');

        case 'send_email':
            return $userController->sendEmail();

        default:
            return $twig->render('404.html.twig');
    }
}
