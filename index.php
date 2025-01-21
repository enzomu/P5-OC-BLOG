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
use Enzo\P5OcBlog\Services\RequestManager;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Routing/Router.php';

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

session_start();

$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader, [
    'cache' => false,
]);
$twig->addGlobal('session', $_SESSION);

$dbManager = new DbManager();
$pdo = $dbManager->getPdo();

$requestManager = new RequestManager();

$commentRepository = new CommentRepository($pdo);
$postRepository = new PostRepository($pdo);
$userRepository = new UserRepository($pdo);

$userService = new UserService($userRepository);
$commentService = new CommentService($commentRepository);
$postService = new PostService($postRepository);

$userController = new UserController(
    $userService,
    $twig,
    $userRepository,
    $requestManager
);

$authentificationService = new AuthenticationService($userController);

$postController = new PostController(
    $postService,
    $commentService,
    $twig,
    $userController,
    $userService,
    $authentificationService,
    $requestManager
);

$commentController = new CommentController(
    $commentService,
    $twig,
    $authentificationService,
    $requestManager
);

$page = $requestManager->getGet('page', 'home');

$validPages = [
    'home', 'post', 'blog_list', 'edit_post', 'login', 'register',
    'login_user', 'register_user', 'logout', 'create_post',
    'update_post', 'delete_post', 'create_comment',
    'update_comment', 'delete_comment', 'send_email',
];

if (!in_array($page, $validPages)) {
    $page = '404.html.twig';
}

$params = [];

$userInfo = $userController->getUserInfo();
$params['isLoggedIn'] = $userInfo['isLoggedIn'];
$params['username'] = $userInfo['username'];

$response = handleRouting($page, $params, $userController, $postController, $commentController, $twig);

$decodedResponse = json_decode($response, true);
if (is_array($decodedResponse) && isset($decodedResponse['redirect'])) {
    header('Location: ' . $decodedResponse['redirect']);
    exit;
}

echo $response;