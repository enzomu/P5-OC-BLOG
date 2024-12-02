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
require_once __DIR__ . '/src/Routing/Router.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader, [
    'cache' => false,
]);

$twig->addGlobal('session', $_SESSION);

$dbManager = new DbManager();
$pdo = $dbManager->getPdo();

$commentRepository = new CommentRepository($pdo);
$postRepository = new PostRepository($pdo);
$userRepository = new UserRepository($pdo);

$userService = new UserService($userRepository);
$commentService = new CommentService($commentRepository);
$postService = new PostService($postRepository);

$userController = new UserController($userService, $twig, $userRepository);
$authentificationService = new AuthenticationService($userController);

$postController = new PostController($postService, $commentService, $twig, $userController, $userService, $authentificationService);
$commentController = new CommentController($commentService, $twig, $authentificationService);

$page = $_GET['page'] ?? 'home';

$validPages = [
    'home', 'post', 'blog_list', 'edit_post', 'login', 'register',
    'login_user', 'register_user', 'logout', 'create_post',
    'update_post', 'delete_post', 'create_comment',
    'update_comment', 'delete_comment'
];

if (!in_array($page, $validPages)) {
    $page = '404.html.twig';
}

$params = [];

$userInfo = $userController->getUserInfo();
$params['isLoggedIn'] = $userInfo['isLoggedIn'];
$params['username'] = $userInfo['username'];

handleRouting($page, $params, $userController, $postController, $commentController, $twig);
