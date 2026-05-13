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

        /* Modal panel (éxito / error) */
        .panel-modal-root {
            position: fixed;
            inset: 0;
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.28s ease, visibility 0.28s ease;
        }
        .panel-modal-root.is-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        .panel-modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.72);
            backdrop-filter: blur(6px);
        }
        .panel-modal-dialog {
            position: relative;
            width: 100%;
            max-width: 420px;
            background: var(--card);
            border: 1px solid #334155;
            border-radius: 20px;
            padding: 28px 26px 24px;
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.45);
            transform: translateY(16px) scale(0.98);
            opacity: 0;
            transition: transform 0.32s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.28s ease;
        }
        .panel-modal-root.is-open .panel-modal-dialog {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        .panel-modal-dialog--success { border-color: rgba(16, 185, 129, 0.45); }
        .panel-modal-dialog--error { border-color: rgba(244, 63, 94, 0.45); }
        .panel-modal-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }
        .panel-modal-dialog--success .panel-modal-icon {
            background: rgba(16, 185, 129, 0.18);
            color: var(--success);
        }
        .panel-modal-dialog--error .panel-modal-icon {
            background: rgba(244, 63, 94, 0.18);
            color: var(--danger);
        }
        .panel-modal-icon svg { width: 28px; height: 28px; }
        .panel-modal-title {
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0 0 6px;
            letter-spacing: -0.02em;
        }
        .panel-modal-dialog--success .panel-modal-title { color: var(--success); }
        .panel-modal-dialog--error .panel-modal-title { color: var(--danger); }
        .panel-modal-meta {
            display: grid;
            gap: 10px;
            margin: 18px 0 16px;
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        .panel-modal-meta dt {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 2px;
        }
        .panel-modal-meta dd {
            margin: 0;
            color: var(--text);
            font-weight: 600;
            font-size: 1rem;
        }
        .panel-modal-desc {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.5;
            margin: 0 0 22px;
        }
        .panel-modal-msg {
            font-size: 0.95rem;
            color: var(--text);
            line-height: 1.55;
            margin: 0 0 22px;
        }
        .panel-modal-actions { display: flex; justify-content: flex-end; gap: 10px; }
        .panel-modal-btn {
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 0.9rem;
            font-family: inherit;
            cursor: pointer;
            background: var(--accent);
            color: var(--white);
        }
        .panel-modal-btn:hover { filter: brightness(1.08); }
        .panel-modal-root[hidden] { display: none !important; }
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
                <h1 style="font-size: 2rem;">Panel Personal</h1>
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

        <?php if ($cuentaOrigen !== null): ?>
        <section class="quick-ops-section" aria-label="Operaciones rápidas">
            <h3>Operaciones rápidas</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin: -8px 0 18px;">
                Cuenta: <strong><?php echo htmlspecialchars($cuentaOrigen->getNumeroCuenta(), ENT_QUOTES, 'UTF-8'); ?></strong>
            </p>
            <div class="quick-ops-grid">
                <form class="quick-op-card" method="post" action="index.php?route=depositar" autocomplete="off">
                    <span class="op-title">Depositar</span>
                    <input type="text" name="monto" inputmode="decimal" placeholder="Monto a depositar ($   0.00)" required>
                    <button type="submit" class="quick-op-btn">Depositar</button>
                </form>
                <form class="quick-op-card" method="post" action="index.php?route=retirar" autocomplete="off">
                    <span class="op-title">Retirar</span>
                    <input type="text" name="monto" inputmode="decimal" placeholder="Monto a retirar ($0.00)" required>
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

    <?php
    $panelModalFlash = null;
    if (!empty($_SESSION['panel_ok'])) {
        $rawOk = $_SESSION['panel_ok'];
        unset($_SESSION['panel_ok']);
        if (is_array($rawOk)) {
            $montoFmt = isset($rawOk['monto_formateado'])
                ? (string) $rawOk['monto_formateado']
                : ('$' . number_format((float) ($rawOk['monto'] ?? 0), 2));
            $panelModalFlash = [
                'variant' => 'success',
                'tipo' => (string) ($rawOk['tipo'] ?? 'Operación'),
                'monto_formateado' => $montoFmt,
                'fecha' => (string) ($rawOk['fecha'] ?? date('d/m/Y H:i:s')),
                'descripcion' => (string) ($rawOk['descripcion'] ?? ''),
            ];
        } else {
            $panelModalFlash = [
                'variant' => 'success',
                'tipo' => 'Operación',
                'monto_formateado' => '—',
                'fecha' => date('d/m/Y H:i:s'),
                'descripcion' => (string) $rawOk,
            ];
        }
    } elseif (!empty($_SESSION['panel_error'])) {
        $panelModalFlash = [
            'variant' => 'error',
            'mensaje' => (string) $_SESSION['panel_error'],
        ];
        unset($_SESSION['panel_error']);
    }
    ?>

    <div
        id="panel-modal-root"
        class="panel-modal-root"
        <?php echo $panelModalFlash === null ? 'hidden' : ''; ?>
        aria-hidden="true"
    >
        <div class="panel-modal-backdrop" id="panel-modal-backdrop"></div>
        <div
            class="panel-modal-dialog"
            id="panel-modal-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="panel-modal-title"
        >
            <div class="panel-modal-icon" id="panel-modal-icon" aria-hidden="true"></div>
            <h2 class="panel-modal-title" id="panel-modal-title"></h2>

            <div id="panel-modal-success-block" hidden>
                <dl class="panel-modal-meta">
                    <div>
                        <dt>Tipo de operación</dt>
                        <dd id="panel-modal-tipo"></dd>
                    </div>
                    <div>
                        <dt>Monto</dt>
                        <dd id="panel-modal-monto"></dd>
                    </div>
                    <div>
                        <dt>Fecha y hora</dt>
                        <dd id="panel-modal-fecha"></dd>
                    </div>
                </dl>
                <p class="panel-modal-desc" id="panel-modal-descripcion"></p>
            </div>

            <div id="panel-modal-error-block" hidden>
                <p class="panel-modal-msg" id="panel-modal-mensaje"></p>
            </div>

            <div class="panel-modal-actions">
                <button type="button" class="panel-modal-btn" id="panel-modal-close">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        const socket = new WebSocket('ws://localhost:8080');
        socket.onopen = function() {
            console.log("Conectado al servidor de WebSockets del Banco");
        };
    </script>
    <script>
        (function () {
            var payload = <?php echo json_encode($panelModalFlash, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
            if (!payload) {
                return;
            }

            var root = document.getElementById('panel-modal-root');
            var backdrop = document.getElementById('panel-modal-backdrop');
            var iconEl = document.getElementById('panel-modal-icon');
            var titleEl = document.getElementById('panel-modal-title');
            var successBlock = document.getElementById('panel-modal-success-block');
            var errorBlock = document.getElementById('panel-modal-error-block');
            var closeBtn = document.getElementById('panel-modal-close');

            var svgCheck = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
            var svgWarn = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';

            function onEscKey(e) {
                if (e.key === 'Escape' && root.classList.contains('is-open')) {
                    closeModal();
                }
            }

            function closeModal() {
                document.removeEventListener('keydown', onEscKey);
                root.classList.remove('is-open');
                root.setAttribute('aria-hidden', 'true');
                setTimeout(function () {
                    root.setAttribute('hidden', '');
                }, 280);
            }

            function openModal() {
                root.removeAttribute('hidden');
                root.setAttribute('aria-hidden', 'false');
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        root.classList.add('is-open');
                    });
                });
            }

            var dialog = document.getElementById('panel-modal-dialog');

            if (payload.variant === 'success') {
                dialog.classList.add('panel-modal-dialog--success');
                dialog.classList.remove('panel-modal-dialog--error');
                iconEl.innerHTML = svgCheck;
                titleEl.textContent = 'Operación exitosa';
                document.getElementById('panel-modal-tipo').textContent = payload.tipo || '';
                document.getElementById('panel-modal-monto').textContent = payload.monto_formateado || '';
                document.getElementById('panel-modal-fecha').textContent = payload.fecha || '';
                document.getElementById('panel-modal-descripcion').textContent = payload.descripcion || '';
                successBlock.hidden = false;
                errorBlock.hidden = true;
            } else {
                dialog.classList.add('panel-modal-dialog--error');
                dialog.classList.remove('panel-modal-dialog--success');
                iconEl.innerHTML = svgWarn;
                titleEl.textContent = 'Operación no realizada';
                document.getElementById('panel-modal-mensaje').textContent = payload.mensaje || '';
                successBlock.hidden = true;
                errorBlock.hidden = false;
            }

            closeBtn.addEventListener('click', closeModal);
            backdrop.addEventListener('click', closeModal);
            document.addEventListener('keydown', onEscKey);

            openModal();
        })();
    </script>
</body>
</html>
