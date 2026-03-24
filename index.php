<?php
require_once __DIR__ . '/models/Roles/Administrador.php';
require_once __DIR__ . '/models/Roles/Cliente.php';
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
    400.00,
    true
);

$cuentaDestino = new Cuenta(
    2,
    $cliente->getId(),
    '001-000002',
    50.00,
    true
);

$depositoExitoso = $cuentaOrigen->depositar(150.00);
$transferenciaExitosa = $cuentaOrigen->transferir($cuentaDestino, 500.00);
$depositoEstado = $depositoExitoso ? 'Exitoso' : 'Fallido';
$depositoMensaje = $depositoExitoso
    ? 'Deposito realizado correctamente (+$150.00).'
    : 'No se pudo realizar el deposito.';

$transferenciaEstado = $transferenciaExitosa ? 'Exitoso' : 'Fallido';
$transferenciaMensaje = $transferenciaExitosa
    ? 'Transferencia realizada correctamente.'
    : 'Transferencia rechazada por saldo insuficiente o cuenta inactiva.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Bancario Simulado</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <main class="container">
        <header class="hero">
            <h1>Sistema Bancario Simulado</h1>
            <p>Demostracion de POO con roles, cuentas y operaciones bancarias.</p>
        </header>

        <section class="grid">
            <article class="card">
                <h2>Administrador</h2>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($administrador->getNombres(), ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Cedula:</strong> <?php echo htmlspecialchars($administrador->getCedula(), ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Permisos:</strong></p>
                <ul>
                    <?php foreach ($administrador->obtenerPermisos() as $permiso): ?>
                        <li><?php echo htmlspecialchars($permiso, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </article>

            <article class="card">
                <h2>Cliente</h2>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($cliente->getNombres(), ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Cedula:</strong> <?php echo htmlspecialchars($cliente->getCedula(), ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Permisos:</strong></p>
                <ul>
                    <?php foreach ($cliente->obtenerPermisos() as $permiso): ?>
                        <li><?php echo htmlspecialchars($permiso, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </article>
        </section>

        <section class="card full">
            <h2>Resultado de operaciones</h2>
            <div class="results">
                <div class="result-item">
                    <h3>Deposito</h3>
                    <p><strong>Estado:</strong> <span class="<?php echo $depositoExitoso ? 'ok' : 'fail'; ?>"><?php echo $depositoEstado; ?></span></p>
                    <p><?php echo htmlspecialchars($depositoMensaje, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="result-item">
                    <h3>Transferencia ($500.00)</h3>
                    <p><strong>Estado:</strong> <span class="<?php echo $transferenciaExitosa ? 'ok' : 'fail'; ?>"><?php echo $transferenciaEstado; ?></span></p>
                    <p><?php echo htmlspecialchars($transferenciaMensaje, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
        </section>

        <section class="grid balances">
            <article class="card">
                <h2>Cuenta Origen</h2>
                <p><strong>Numero:</strong> <?php echo htmlspecialchars($cuentaOrigen->getNumeroCuenta(), ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Saldo final:</strong> $<?php echo number_format($cuentaOrigen->getSaldo(), 2); ?></p>
            </article>
            <article class="card">
                <h2>Cuenta Destino</h2>
                <p><strong>Numero:</strong> <?php echo htmlspecialchars($cuentaDestino->getNumeroCuenta(), ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Saldo final:</strong> $<?php echo number_format($cuentaDestino->getSaldo(), 2); ?></p>
            </article>
        </section>
    </main>
</body>
</html>
