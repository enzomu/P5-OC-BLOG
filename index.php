<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/vendor/autoload.php';

$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader, [
    'cache' => false,
]);

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

$validPages = ['home', 'post', 'blog_list', 'edit_post'];
if (!in_array($page, $validPages)) {
    $page = 'home'; // Valeur par défaut si la page est invalide
}

echo $twig->render("$page.html.twig");