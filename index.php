<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Controllers\AuthController;
use App\Controllers\BancoController;

$route = $_GET['route'] ?? 'login';
$route = is_string($route) ? strtolower(trim($route)) : 'login';

switch ($route) {
    case 'login':
        (new AuthController())->login();
        break;
    case 'registro':
        (new AuthController())->registro();
        break;
    case 'logout':
        (new AuthController())->logout();
        break;
    case 'panel':
        (new BancoController())->mostrarPanel();
        break;
    default:
        (new AuthController())->login();
        break;
}
