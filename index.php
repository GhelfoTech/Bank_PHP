<?php

require_once __DIR__ . '/models/Administrador.php';
require_once __DIR__ . '/models/Cliente.php';
require_once __DIR__ . '/models/Cuenta.php';

/**
 * Punto de entrada de prueba del sistema bancario simulado.
 *
 * Demuestra:
 * 1) Instanciacion de Administrador y Cliente.
 * 2) Deposito exitoso.
 * 3) Transferencia fallida por saldo insuficiente.
 */

$administrador = new Administrador(
    1,
    '0102030405',
    'Ana Admin',
    'ana.admin@banco.local',
    'admin123'
);

$cliente = new Cliente(
    2,
    '1717171717',
    'Carlos Cliente',
    'carlos.cliente@banco.local',
    'cliente123'
);

$cuentaOrigen = new Cuenta(
    1,
    $cliente->getId(),
    '001-000001',
    100.00,
    true
);

$cuentaDestino = new Cuenta(
    2,
    $cliente->getId(),
    '001-000002',
    50.00,
    true
);

echo "=== Sistema Bancario Simulado (POO) ===" . PHP_EOL . PHP_EOL;

echo "Administrador: " . $administrador->getNombres() . PHP_EOL;
echo "Permisos del administrador: " . implode(', ', $administrador->obtenerPermisos()) . PHP_EOL . PHP_EOL;

echo "Cliente: " . $cliente->getNombres() . PHP_EOL;
echo "Permisos del cliente: " . implode(', ', $cliente->obtenerPermisos()) . PHP_EOL . PHP_EOL;

echo "Saldo inicial cuenta origen: $" . number_format($cuentaOrigen->getSaldo(), 2) . PHP_EOL;
$depositoExitoso = $cuentaOrigen->depositar(150.00);
echo $depositoExitoso
    ? "Deposito realizado correctamente (+$150.00)." . PHP_EOL
    : "No se pudo realizar el deposito." . PHP_EOL;
echo "Saldo luego del deposito: $" . number_format($cuentaOrigen->getSaldo(), 2) . PHP_EOL . PHP_EOL;

echo "Intentando transferencia de $500.00 (debe fallar por saldo insuficiente)..." . PHP_EOL;
$transferenciaExitosa = $cuentaOrigen->transferir($cuentaDestino, 500.00);
echo $transferenciaExitosa
    ? "Transferencia exitosa." . PHP_EOL
    : "Transferencia rechazada por saldo insuficiente o cuenta inactiva." . PHP_EOL;

echo PHP_EOL;
echo "Saldo final cuenta origen: $" . number_format($cuentaOrigen->getSaldo(), 2) . PHP_EOL;
echo "Saldo final cuenta destino: $" . number_format($cuentaDestino->getSaldo(), 2) . PHP_EOL;
