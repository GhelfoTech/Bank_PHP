<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers/auth.php';

$route = $_GET['route'] ?? 'login';

$route = is_string($route) ? strtolower(trim($route)) : 'login';

switch ($route) {

    case 'login':
        auth_login();
        break;

    case 'registro':
        auth_registro();
        break;

    case 'logout':
        logout();
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

    case 'toggle_estado':
        (new \App\Controllers\BancoController())->toggleEstado();
        break;

    case 'reporte_pdf':
        (new \App\Controllers\BancoController())->generarReportePdf();
        break;

    default:
        auth_login();
        break;
}
