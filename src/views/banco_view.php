<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Josstor</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/styles.css">
    <style>
        .quick-ops-section { margin-bottom: 40px; }
        .quick-ops-section h3 { font-size: 1.2rem; margin-bottom: 18px; font-weight: 600; }
        .quick-ops-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .quick-op-card {
            background: var(--card);
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 22px 24px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .quick-op-card .op-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted); }
        .quick-op-card input[type="text"],
        .quick-op-card input[type="number"] {
            width: 100%;
            padding: 11px 14px;
            border-radius: 10px;
            border: 1px solid #334155;
            background: #0f172a;
            color: var(--text);
            font-size: 0.95rem;
            font-family: inherit;
            box-sizing: border-box;
        }
        .quick-op-card input:focus { outline: none; border-color: var(--accent); }
        .quick-op-btn {
            padding: 11px 16px;
            border: none;
            border-radius: 10px;
            background: var(--accent);
            color: var(--white);
            font-weight: 600;
            font-size: 0.9rem;
            font-family: inherit;
            cursor: pointer;
            align-self: flex-start;
        }
        .quick-op-btn:hover { filter: brightness(1.08); }
    </style>
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

        <?php if (!empty($_SESSION['panel_ok'])): ?>
            <div class="msg-ok"><?php echo htmlspecialchars((string) $_SESSION['panel_ok'], ENT_QUOTES, 'UTF-8'); ?></div>
            <?php unset($_SESSION['panel_ok']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['panel_error'])): ?>
            <div class="msg-err"><?php echo htmlspecialchars((string) $_SESSION['panel_error'], ENT_QUOTES, 'UTF-8'); ?></div>
            <?php unset($_SESSION['panel_error']); ?>
        <?php endif; ?>

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

        <?php if ($cuentaOrigen !== null): ?>
        <section class="quick-ops-section" aria-label="Operaciones rápidas">
            <h3>Operaciones rápidas</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin: -8px 0 18px;">
                Cuenta: <strong><?php echo htmlspecialchars($cuentaOrigen->getNumeroCuenta(), ENT_QUOTES, 'UTF-8'); ?></strong>
            </p>
            <div class="quick-ops-grid">
                <form class="quick-op-card" method="post" action="index.php?route=depositar" autocomplete="off">
                    <span class="op-title">Depositar</span>
                    <input type="text" name="monto" inputmode="decimal" placeholder="Monto a depositar (0.00)" required>
                    <button type="submit" class="quick-op-btn">Depositar</button>
                </form>
                <form class="quick-op-card" method="post" action="index.php?route=retirar" autocomplete="off">
                    <span class="op-title">Retirar</span>
                    <input type="text" name="monto" inputmode="decimal" placeholder="Monto a retirar (0.00)" required>
                    <button type="submit" class="quick-op-btn">Retirar</button>
                </form>
            </div>
        </section>
        <?php endif; ?>

        <section class="activity-log">
            <h3 style="margin-bottom: 25px; font-size: 1.2rem;">Trazabilidad de Movimientos</h3>

            <?php if (empty($historial)): ?>
            <p style="color: var(--text-muted); margin: 0;">No hay actividad reciente en esta cuenta</p>
            <?php else: ?>
                <?php foreach ($historial as $mov): ?>
                    <?php
                    $tipoRaw = $mov->getTipo();
                    $tipoEtiqueta = match ($tipoRaw) {
                        'deposito' => 'Depósito',
                        'retiro' => 'Retiro',
                        'transferencia' => 'Transferencia',
                        default => ucfirst($tipoRaw),
                    };
                    $claseLog = match ($tipoRaw) {
                        'deposito' => 'success',
                        'retiro' => 'error',
                        default => '',
                    };
                    $icono = match ($tipoRaw) {
                        'deposito' => '+',
                        'retiro' => '−',
                        default => '⇄',
                    };
                    $bgIcono = match ($tipoRaw) {
                        'deposito' => 'rgba(16, 185, 129, 0.2)',
                        'retiro' => 'rgba(239, 68, 68, 0.2)',
                        default => 'rgba(148, 163, 184, 0.2)',
                    };
                    $colorIcono = match ($tipoRaw) {
                        'deposito' => 'var(--success)',
                        'retiro' => 'var(--danger)',
                        default => 'var(--text-muted)',
                    };
                    $prefijoMonto = match ($tipoRaw) {
                        'deposito' => '+',
                        'retiro' => '−',
                        default => '',
                    };
                    $colorMonto = match ($tipoRaw) {
                        'deposito' => 'var(--success)',
                        'retiro' => 'var(--danger)',
                        default => 'var(--text-muted)',
                    };
                    $montoAbs = number_format(abs($mov->getMonto()), 2);
                    ?>
            <div class="log-entry <?php echo htmlspecialchars($claseLog); ?>">
                <div class="log-icon" style="background: <?php echo $bgIcono; ?>; color: <?php echo $colorIcono; ?>; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold;"><?php echo htmlspecialchars($icono); ?></div>
                <div class="log-info">
                    <b><?php echo htmlspecialchars($tipoEtiqueta); ?></b>
                    <span style="display: block; margin-top: 4px; font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($mov->getFecha()); ?></span>
                    <span><?php echo htmlspecialchars($mov->getDescripcion()); ?></span>
                </div>
                <div style="font-weight: 700; font-size: 1.1rem; color: <?php echo $colorMonto; ?>; white-space: nowrap;">
                    <?php echo $prefijoMonto !== '' ? htmlspecialchars($prefijoMonto) : ''; ?>$<?php echo $montoAbs; ?>
                </div>
            </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
