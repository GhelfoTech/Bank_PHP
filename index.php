<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Controllers\BancoController;

$controller = new BancoController();
$controller->mostrarPanel();