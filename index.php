<?php

use Enzo\P5OcBlog\Controllers\UserController;
use Enzo\P5OcBlog\Repository\UserRepository;
use Enzo\P5OcBlog\Services\DbManager;
use Enzo\P5OcBlog\Services\UserService;
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

$userRepository = new UserRepository($pdo);
$userService = new UserService($userRepository);
$userController = new UserController($userService, $twig);

$page = $_GET['page'] ?? 'home';

$validPages = ['home', 'post', 'blog_list', 'edit_post', 'login', 'register', 'login_user', 'register_user', 'logout'];
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
        break;

    case 'post':
        echo $twig->render('post.html.twig', $params);
        break;

    case 'edit_post':
        echo $twig->render('edit_post.html.twig', $params);
        break;

    default:
        echo $twig->render('404.html.twig');
        break;
}
