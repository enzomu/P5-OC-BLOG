<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$request = Request::createFromGlobals();

($request->getPathInfo() === '/');

$response = new Response('Hello, World!', Response::HTTP_OK);

$response->send();