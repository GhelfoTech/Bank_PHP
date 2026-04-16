<?php

// Carga de Modelos
require_once __DIR__ . '/models/Roles/Administrador.php';
require_once __DIR__ . '/models/Roles/Cliente.php';
require_once __DIR__ . '/models/Cuenta.php';

// Carga del Controlador
require_once __DIR__ . '/controllers/BancoController.php';

$controller = new BancoController();
$controller->mostrarPanel();