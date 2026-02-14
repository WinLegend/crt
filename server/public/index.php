<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Nesneek\Controllers\AuthController;
use Nesneek\Controllers\FileController;
use Nesneek\Controllers\AdminController;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove /api prefix if present (depends on Nginx config, but for safety)
$uri = str_replace('/api', '', $uri);
$parts = explode('/', trim($uri, '/'));

// Simple Router
if ($parts[0] === 'auth') {
    $controller = new AuthController();
    if ($parts[1] === 'register' && $method === 'POST') {
        $controller->register();
    } elseif ($parts[1] === 'login' && $method === 'POST') {
        $controller->login();
    } elseif ($parts[1] === 'me' && $method === 'GET') {
        $controller->me();
    }
} elseif ($parts[0] === 'files') {
    $controller = new FileController();
    if ($parts[1] === 'upload' && $method === 'POST') {
        $controller->upload();
    } elseif ($parts[1] === 'download' && $method === 'GET') {
        $controller->download($parts[2]);
    } elseif ($parts[1] === 'my-files' && $method === 'GET') {
        $controller->myFiles();
    }
} elseif ($parts[0] === 'admin') {
    $controller = new AdminController();
    if ($parts[1] === 'users' && $method === 'GET') {
        $controller->getUsers();
    } elseif ($parts[1] === 'users' && $parts[3] === 'block' && $method === 'PUT') {
        $controller->toggleBlock($parts[2]);
    } elseif ($parts[1] === 'files' && $method === 'GET') {
        $controller->getFiles();
    } elseif ($parts[1] === 'files' && $method === 'DELETE') {
        $controller->deleteFile($parts[2]);
    } elseif ($parts[1] === 'invites' && $method === 'POST') {
        $controller->generateInvites();
    } elseif ($parts[1] === 'stats' && $method === 'GET') {
        $controller->getStats();
    }
} else {
    http_response_code(404);
    echo json_encode(["message" => "Endpoint not found"]);
}
