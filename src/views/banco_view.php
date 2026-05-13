<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Josstor</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/styles.css">
</head>
<body>
    <aside class="sidebar">
        <h2 style="margin-bottom: 40px; color: var(--accent)">Bank Josstor.</h2>

        <div class="admin-card">
            <h4>Sesión activa</h4>
            <div class="admin-data">
                <p><b>Nombre:</b> <?php echo htmlspecialchars($usuario->getNombres()); ?></p>
                <p><b>Rol:</b> <?php echo htmlspecialchars(ucfirst($usuario->getRol())); ?></p>
                <p><b>ID:</b> #<?php echo $usuario->getId(); ?></p>
                <p><b>Cédula:</b> <?php echo htmlspecialchars($usuario->getCedula()); ?></p>
                <p><b>Email:</b> <small><?php echo htmlspecialchars($usuario->getEmail()); ?></small></p>
            </div>
        </div>

        <p style="margin-bottom: 12px; color: var(--text-muted); font-size: 0.9rem;">
            <a href="index.php?route=logout" style="color: var(--danger); text-decoration: none; font-weight: 600;">Cerrar sesión</a>
        </p>

        <nav style="color: var(--text-muted); font-size: 0.9rem;">
            <p style="margin-bottom: 15px;">Permisos:</p>
            <ul style="list-style: none; padding-left: 5px;">
                <?php foreach ($usuario->obtenerPermisos() as $permiso): ?>
                    <li style="margin-bottom: 8px;">✓ <?php echo str_replace('_', ' ', $permiso); ?></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </aside>

    <main class="main-view">
        <header class="top-bar">
            <div>
                <h1 style="font-size: 2rem;">Panel</h1>
                <div class="client-tag">
                    <?php echo htmlspecialchars($usuario->getNombres()); ?> · <?php echo htmlspecialchars(ucfirst($usuario->getRol())); ?>
                </div>
            </div>
            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
                <span class="status-badge">SISTEMA ONLINE</span>
                <a href="index.php?route=logout" style="color: var(--text-muted); font-size: 0.85rem;">Cerrar sesión</a>
            </div>
        </header>

        <?php if ($cuentaOrigen !== null): ?>
        <section class="accounts-grid">
            <div class="bank-card">
                <span class="balance-label">CUENTA PRINCIPAL (ORIGEN)</span>
                <div class="balance-value">$<?php echo number_format($cuentaOrigen->getSaldo(), 2); ?></div>
                <div class="card-footer">
                    <div class="footer-item">
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Número de Cuenta</span>
                        <b><?php echo htmlspecialchars($cuentaOrigen->getNumeroCuenta()); ?></b>
                    </div>
                    <div class="footer-item" style="text-align: right;">
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Estado</span>
                        <b style="color: var(--success)">ACTIVA</b>
                    </div>
                </div>
            </div>

            <?php if ($cuentaDestino !== null): ?>
            <div class="bank-card" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                <span class="balance-label">CUENTA DESTINO</span>
                <div class="balance-value">$<?php echo number_format($cuentaDestino->getSaldo(), 2); ?></div>
                <div class="card-footer">
                    <div class="footer-item">
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Número de Cuenta</span>
                        <b><?php echo htmlspecialchars($cuentaDestino->getNumeroCuenta()); ?></b>
                    </div>
                    <div class="footer-item" style="text-align: right;">
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Propietario ID</span>
                        <b>#<?php echo $cuentaDestino->getUsuarioId(); ?></b>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </section>
        <?php else: ?>
        <section class="accounts-grid" style="margin-bottom: 30px;">
            <p style="color: var(--text-muted);">No hay cuentas bancarias para mostrar en este usuario (por ejemplo, un administrador sin cuentas, o un cliente sin registros en la tabla cuentas).</p>
        </section>
        <?php endif; ?>

        <section class="activity-log">
            <h3 style="margin-bottom: 25px; font-size: 1.2rem;">Trazabilidad de Movimientos</h3>

            <?php if ($cuentaOrigen !== null): ?>
            <div class="log-entry success">
                <div class="log-icon" style="background: rgba(16, 185, 129, 0.2); color: var(--success); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold;">+</div>
                <div class="log-info">
                    <b>Depósito</b>
                    <span><?php echo htmlspecialchars($depositoMensaje); ?></span>
                </div>
                <div style="font-weight: 700; font-size: 1.1rem; color: var(--success)">+$150.00</div>
            </div>
            <?php endif; ?>

            <div class="log-entry <?php echo ($cuentaDestino !== null && $transferenciaExitosa) ? 'success' : (($cuentaDestino !== null) ? 'error' : ''); ?>">
                <div class="log-icon" style="background: <?php echo ($cuentaDestino !== null && $transferenciaExitosa) ? 'rgba(16, 185, 129, 0.2)' : 'rgba(148, 163, 184, 0.2)'; ?>; color: <?php echo ($cuentaDestino !== null && $transferenciaExitosa) ? 'var(--success)' : 'var(--text-muted)'; ?>; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                    <?php echo ($cuentaDestino !== null) ? ($transferenciaExitosa ? '⇄' : '✕') : '—'; ?>
                </div>
                <div class="log-info">
                    <b>Transferencia</b>
                    <span><?php echo htmlspecialchars($transferenciaMensaje); ?></span>
                </div>
                <?php if ($cuentaDestino !== null): ?>
                <div style="font-weight: 700; font-size: 1.1rem; color: <?php echo $transferenciaExitosa ? 'var(--success)' : 'var(--danger)'; ?>">
                    $500.00
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
        const socket = new WebSocket('ws://localhost:8080');
        socket.onopen = function() {
            console.log("Conectado al servidor de WebSockets del Banco");
        };
    </script>
</body>
</html>
