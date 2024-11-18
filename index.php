<?php

use Enzo\P5OcBlog\Controllers\UserController;
use Enzo\P5OcBlog\Controllers\PostController;
use Enzo\P5OcBlog\Controllers\CommentController;
use Enzo\P5OcBlog\Repository\UserRepository;
use Enzo\P5OcBlog\Repository\PostRepository;
use Enzo\P5OcBlog\Repository\CommentRepository;
use Enzo\P5OcBlog\Services\AuthenticationService;
use Enzo\P5OcBlog\Services\DbManager;
use Enzo\P5OcBlog\Services\UserService;
use Enzo\P5OcBlog\Services\PostService;
use Enzo\P5OcBlog\Services\CommentService;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader, [
    'cache' => false,
]);

$dbManager = new DbManager();
$pdo = $dbManager->getPdo();

$commentRepository = new CommentRepository($pdo);
$postRepository = new PostRepository($pdo);
$userRepository = new UserRepository($pdo);

$userService = new UserService($userRepository);
$commentService = new CommentService($commentRepository);
$postService = new PostService($postRepository);

$userController = new UserController($userService, $twig);

$authentificationService = new AuthenticationService($userController);



$postController = new PostController($postService, $commentService, $twig, $userController, $authentificationService);
$commentController = new CommentController($commentService, $twig, $authentificationService);

$page = $_GET['page'] ?? 'home';

$validPages = ['home', 'post', 'blog_list', 'edit_post', 'login', 'register', 'login_user', 'register_user', 'logout', 'create_post', 'update_post', 'delete_post', 'create_comment', 'update_comment', 'delete_comment'];
if (!in_array($page, $validPages)) {
    $page = '404.html.twig';
}

$params = [];

$userInfo = $userController->getUserInfo();
$params['isLoggedIn'] = $userInfo['isLoggedIn'];
$params['username'] = $userInfo['username'];

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

/*    case 'post':
        $postId = $_GET['id'] ?? null;
        if ($postId) {
            $post = $postService->findPostById($postId);
            $comments = $commentService->getCommentsByPostId($postId);
            $params['post'] = $post;
            $params['comments'] = $comments;
            echo $twig->render('post.html.twig', $params);
        }
        break;*/

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
