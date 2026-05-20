<?php

declare(strict_types=1);

$route = $_GET['route'] ?? 'login';

$route = is_string($route) ? strtolower(trim($route)) : 'login';

switch ($route) {

    case 'login':
        (new \App\Config\AuthController())->login();
        break;

    case 'registro':
        (new \App\Config\AuthController())->registro();
        break;

    case 'logout':
        (new \App\Config\AuthController())->logout();
        break;

    case 'panel':
        (new \App\Controllers\BancoController())->mostrarPanel();
        break;

    case 'depositar':
        (new \App\Controllers\BancoController())->procesarDeposito();
        break;

    case 'retirar':
        (new \App\Controllers\BancoController())->procesarRetiro();
        break;

    case 'transferir':
        (new \App\Controllers\BancoController())->procesarTransferencia();
        break;

    default:
        (new \App\Config\AuthController())->login();
        break;
}