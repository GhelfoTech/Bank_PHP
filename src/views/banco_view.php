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
                    <input type="text" name="monto" inputmode="decimal" placeholder="Monto a depositar ($0.00)" required>
                    <button type="submit" class="quick-op-btn">Depositar</button>
                </form>
                <form class="quick-op-card" method="post" action="index.php?route=retirar" autocomplete="off">
                    <span class="op-title">Retirar</span>
                    <input type="text" name="monto" inputmode="decimal" placeholder="Monto a retirar ($0.00)" required>
                    <button type="submit" class="quick-op-btn">Retirar</button>
                </form>
            </div>
        </section>

        <section class="transfer-section" aria-label="Transferir a terceros">
            <h3>Transferir a terceros</h3>
            <p class="transfer-section__lead">
                Envío seguro a otra cuenta. Verifique el <strong>número de cuenta</strong> y la <strong>cédula del titular</strong> antes de confirmar.
            </p>
            <form class="transfer-card" method="post" action="index.php?route=transferir" autocomplete="off">
                <span class="op-title">Datos de la transferencia</span>
                <input type="text" name="numero_cuenta_destino" placeholder="Número de cuenta destino" required maxlength="64">
                <input type="text" name="cedula_destino" placeholder="Cédula del titular de la cuenta" required maxlength="32">
                <input type="text" name="monto" inputmode="decimal" placeholder="Monto a transferir ($0.00)" required>
                <textarea name="descripcion_personalizada" placeholder="Motivo o descripción (opcional)" maxlength="500"></textarea>
                <button type="submit" class="transfer-btn">Enviar transferencia</button>
            </form>
        </section>
        <?php endif; ?>

        <?php if ($usuario->getRol() === 'administrador'): ?>
        <section class="activity-log" aria-label="Gestión de usuarios" style="margin-bottom: 40px;">
            <h3 style="margin-bottom: 12px; font-size: 1.2rem;">Gestión de usuarios y cuentas</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 22px;">
                Habilite o deshabilite otros usuarios (y sus cuentas). Genere el reporte PDF de movimientos de cada cuenta.
            </p>
            <?php if (empty($usuariosGestion)): ?>
            <p style="color: var(--text-muted); margin: 0;">No hay otros usuarios registrados en el sistema.</p>
            <?php else: ?>
                <?php foreach ($usuariosGestion as $item): ?>
                    <?php
                    $activo = (bool) $item['estado'];
                    $claseEstado = $activo ? 'success' : 'error';
                    $etiquetaEstado = $activo ? 'ACTIVO' : 'INACTIVO';
                    $colorEstado = $activo ? 'var(--success)' : 'var(--danger)';
                    $textoToggle = $activo ? 'Deshabilitar' : 'Habilitar';
                    $cuentaId = $item['cuenta_id'];
                    $numeroCuenta = $item['numero_cuenta'] !== null ? (string) $item['numero_cuenta'] : 'Sin cuenta';
                    $saldoTexto = $item['saldo'] !== null ? ('$' . number_format((float) $item['saldo'], 2)) : '—';
                    ?>
            <div class="log-entry <?php echo htmlspecialchars($claseEstado, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="log-info" style="margin-left: 0;">
                    <b><?php echo htmlspecialchars((string) $item['nombres'], ENT_QUOTES, 'UTF-8'); ?></b>
                    <span style="display: block; margin-top: 4px;">
                        #<?php echo (int) $item['id']; ?>
                        · <?php echo htmlspecialchars(ucfirst((string) $item['rol']), ENT_QUOTES, 'UTF-8'); ?>
                        · Cédula: <?php echo htmlspecialchars((string) $item['cedula'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span style="display: block; margin-top: 2px;">
                        <?php echo htmlspecialchars((string) $item['email'], ENT_QUOTES, 'UTF-8'); ?>
                        · Cuenta: <?php echo htmlspecialchars($numeroCuenta, ENT_QUOTES, 'UTF-8'); ?>
                        · Saldo: <?php echo htmlspecialchars($saldoTexto, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px; white-space: nowrap;">
                    <b style="color: <?php echo $colorEstado; ?>;"><?php echo $etiquetaEstado; ?></b>
                    <form method="post" action="index.php?route=toggle_estado" style="margin: 0;">
                        <input type="hidden" name="usuario_id" value="<?php echo (int) $item['id']; ?>">
                        <button type="submit" class="quick-op-btn"><?php echo htmlspecialchars($textoToggle, ENT_QUOTES, 'UTF-8'); ?></button>
                    </form>
                    <?php if ($cuentaId !== null): ?>
                    <a href="index.php?route=reporte_pdf&amp;cuenta_id=<?php echo (int) $cuentaId; ?>" class="quick-op-btn" style="text-decoration: none; display: inline-block; text-align: center;">
                        Generar Reporte PDF
                    </a>
                    <?php endif; ?>
                </div>
            </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <?php $historialVistaPrevia = array_slice($historial, 0, 3); ?>
        <section class="activity-log">
            <h3 style="margin-bottom: 25px; font-size: 1.2rem;">Trazabilidad de Movimientos</h3>

            <?php if (empty($historial)): ?>
            <p style="color: var(--text-muted); margin: 0;">No se han registrado movimientos en esta cuenta.</p>
            <?php else: ?>
                <?php foreach ($historialVistaPrevia as $mov): ?>
                    <?php
                    $tipoRaw = $mov->getTipo();
                    $descLower = strtolower($mov->getDescripcion());
                    $tipoEtiqueta = match ($tipoRaw) {
                        'deposito' => 'Depósito',
                        'retiro' => 'Retiro',
                        'transferencia' => 'Transferencia',
                        default => ucfirst($tipoRaw),
                    };
                    $claseLog = match ($tipoRaw) {
                        'deposito' => 'success',
                        'retiro' => 'error',
                        'transferencia' => 'transfer',
                        default => '',
                    };
                    $icono = match ($tipoRaw) {
                        'deposito' => '+',
                        'retiro' => '−',
                        default => '⇄',
                    };
                    if ($tipoRaw === 'transferencia') {
                        $bgIcono = 'rgba(232, 181, 101, 0.16)';
                        $colorIcono = '#e8b565';
                    } else {
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
                    }
                    if ($tipoRaw === 'deposito') {
                        $prefijoMonto = '+';
                        $colorMonto = 'var(--success)';
                    } elseif ($tipoRaw === 'retiro') {
                        $prefijoMonto = '−';
                        $colorMonto = 'var(--danger)';
                    } elseif ($tipoRaw === 'transferencia') {
                        if (str_contains($descLower, 'recibida')) {
                            $prefijoMonto = '+';
                            $colorMonto = 'var(--success)';
                        } elseif (str_contains($descLower, 'enviada')) {
                            $prefijoMonto = '−';
                            $colorMonto = 'var(--danger)';
                        } else {
                            $prefijoMonto = '';
                            $colorMonto = 'var(--text-muted)';
                        }
                    } else {
                        $prefijoMonto = '';
                        $colorMonto = 'var(--text-muted)';
                    }
                    $montoAbs = number_format(abs($mov->getMonto()), 2);
                    $logIconClass = $tipoRaw === 'transferencia' ? 'log-icon log-icon--transfer' : 'log-icon';
                    $logIconStyle = $tipoRaw === 'transferencia'
                        ? 'width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold;'
                        : 'background: ' . $bgIcono . '; color: ' . $colorIcono . '; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold;';
                    ?>
            <div class="log-entry <?php echo htmlspecialchars($claseLog); ?>">
                <div class="<?php echo htmlspecialchars($logIconClass); ?>" style="<?php echo $logIconStyle; ?>"><?php echo htmlspecialchars($icono); ?></div>
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
                <button type="button" class="btn-consultar-operaciones" id="btn-consultar-operaciones" aria-haspopup="dialog" aria-controls="history-modal-root">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    Consultar Operaciones
                </button>
            <?php endif; ?>
        </section>
    </main>

    <?php if (!empty($historial)): ?>
    <div
        id="history-modal-root"
        class="history-modal-root"
        hidden
        aria-hidden="true"
    >
        <div class="history-modal-backdrop" id="history-modal-backdrop"></div>
        <div
            class="history-modal-dialog"
            id="history-modal-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="history-modal-title"
        >
            <div class="history-modal-header">
                <div class="history-modal-header-top">
                    <h2 id="history-modal-title">Historial de operaciones</h2>
                    <button type="button" class="history-modal-close" id="history-modal-close" aria-label="Cerrar historial">&times;</button>
                </div>
                <div class="history-modal-filters" role="group" aria-label="Filtrar movimientos">
                    <button type="button" class="history-filter-btn is-active" data-filter="todos">Todos</button>
                    <button type="button" class="history-filter-btn" data-filter="deposito">Depósitos</button>
                    <button type="button" class="history-filter-btn" data-filter="retiro">Retiros</button>
                    <button type="button" class="history-filter-btn" data-filter="transferencia">Transferencias</button>
                </div>
            </div>
            <div class="history-modal-body" id="history-modal-body">
                <div class="history-modal-table-head" aria-hidden="true">
                    <span>Fecha / hora</span>
                    <span>Tipo</span>
                    <span>Descripción</span>
                    <span>Monto</span>
                </div>
                <?php foreach ($historial as $mov): ?>
                    <?php
                    $tipoRaw = $mov->getTipo();
                    $descLower = strtolower($mov->getDescripcion());
                    $tipoEtiqueta = match ($tipoRaw) {
                        'deposito' => 'Depósito',
                        'retiro' => 'Retiro',
                        'transferencia' => 'Transferencia',
                        default => ucfirst($tipoRaw),
                    };
                    $icono = match ($tipoRaw) {
                        'deposito' => '+',
                        'retiro' => '−',
                        default => '⇄',
                    };
                    if ($tipoRaw === 'transferencia') {
                        $bgIcono = 'rgba(232, 181, 101, 0.16)';
                        $colorIcono = '#e8b565';
                    } else {
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
                    }
                    if ($tipoRaw === 'deposito') {
                        $montoClass = 'history-monto--suma';
                        $prefijoMonto = '+';
                    } elseif ($tipoRaw === 'retiro') {
                        $montoClass = 'history-monto--resta';
                        $prefijoMonto = '−';
                    } elseif ($tipoRaw === 'transferencia') {
                        if (str_contains($descLower, 'recibida')) {
                            $montoClass = 'history-monto--suma';
                            $prefijoMonto = '+';
                        } elseif (str_contains($descLower, 'enviada')) {
                            $montoClass = 'history-monto--resta';
                            $prefijoMonto = '−';
                        } else {
                            $montoClass = 'history-monto--neutro';
                            $prefijoMonto = '';
                        }
                    } else {
                        $montoClass = 'history-monto--neutro';
                        $prefijoMonto = '';
                    }
                    $montoAbs = number_format(abs($mov->getMonto()), 2);
                    $tipoData = htmlspecialchars($tipoRaw, ENT_QUOTES, 'UTF-8');
                    $historyIconClass = 'history-modal-row-icon'
                        . ($tipoRaw === 'transferencia' ? ' history-modal-row-icon--transfer' : '');
                    $historyIconStyle = $tipoRaw === 'transferencia'
                        ? ''
                        : ('background: ' . $bgIcono . '; color: ' . $colorIcono . ';');
                    ?>
                <div class="history-modal-row" data-tipo="<?php echo $tipoData; ?>">
                    <div class="history-modal-row-fecha"><?php echo htmlspecialchars($mov->getFecha(), ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="history-modal-row-tipo">
                        <span class="<?php echo htmlspecialchars($historyIconClass, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $historyIconStyle !== '' ? ' style="' . htmlspecialchars($historyIconStyle, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>><?php echo htmlspecialchars($icono, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span><?php echo htmlspecialchars($tipoEtiqueta, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="history-modal-row-desc"><?php echo htmlspecialchars($mov->getDescripcion(), ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="history-modal-row-monto <?php echo htmlspecialchars($montoClass, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo $prefijoMonto !== '' ? htmlspecialchars($prefijoMonto, ENT_QUOTES, 'UTF-8') : ''; ?>$<?php echo $montoAbs; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php
    $modalBootstrap = null;
    if (!empty($_SESSION['panel_ok'])) {
        $rawOk = $_SESSION['panel_ok'];
        unset($_SESSION['panel_ok']);
        if (is_array($rawOk) && ($rawOk['modal'] ?? '') === 'recibo_transferencia') {
            $modalBootstrap = [
                'kind' => 'recibo_transferencia',
                'beneficiario' => (string) ($rawOk['beneficiario'] ?? ''),
                'numero_cuenta_destino' => (string) ($rawOk['numero_cuenta_destino'] ?? ''),
                'monto_formateado' => isset($rawOk['monto_formateado'])
                    ? (string) $rawOk['monto_formateado']
                    : ('$' . number_format((float) ($rawOk['monto'] ?? 0), 2)),
                'fecha' => (string) ($rawOk['fecha'] ?? date('d/m/Y H:i:s')),
                'descripcion_usuario' => (string) ($rawOk['descripcion_usuario'] ?? ''),
            ];
        } elseif (is_array($rawOk)) {
            $montoFmt = isset($rawOk['monto_formateado'])
                ? (string) $rawOk['monto_formateado']
                : ('$' . number_format((float) ($rawOk['monto'] ?? 0), 2));
            $modalBootstrap = [
                'kind' => 'panel_modal',
                'variant' => 'success',
                'tipo' => (string) ($rawOk['tipo'] ?? 'Operación'),
                'monto_formateado' => $montoFmt,
                'fecha' => (string) ($rawOk['fecha'] ?? date('d/m/Y H:i:s')),
                'descripcion' => (string) ($rawOk['descripcion'] ?? ''),
            ];
        } else {
            $modalBootstrap = [
                'kind' => 'panel_modal',
                'variant' => 'success',
                'tipo' => 'Operación',
                'monto_formateado' => '—',
                'fecha' => date('d/m/Y H:i:s'),
                'descripcion' => (string) $rawOk,
            ];
        }
    } elseif (!empty($_SESSION['panel_error'])) {
        $modalBootstrap = [
            'kind' => 'panel_modal',
            'variant' => 'error',
            'mensaje' => (string) $_SESSION['panel_error'],
        ];
        unset($_SESSION['panel_error']);
    }

    $hidePanelModal = $modalBootstrap === null
        || (($modalBootstrap['kind'] ?? '') === 'recibo_transferencia');
    $hideTransferReceipt = $modalBootstrap === null
        || (($modalBootstrap['kind'] ?? '') !== 'recibo_transferencia');
    ?>

    <div
        id="panel-modal-root"
        class="panel-modal-root"
        <?php echo $hidePanelModal ? 'hidden' : ''; ?>
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

    <div
        id="transfer-receipt-modal-root"
        class="panel-modal-root"
        <?php echo $hideTransferReceipt ? 'hidden' : ''; ?>
        aria-hidden="true"
    >
        <div class="panel-modal-backdrop" id="transfer-receipt-modal-backdrop"></div>
        <div
            class="panel-modal-dialog panel-modal-dialog--transfer-receipt"
            id="transfer-receipt-modal-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="transfer-receipt-title"
        >
            <div class="panel-modal-icon" id="transfer-receipt-modal-icon" aria-hidden="true"></div>
            <p class="panel-modal-subtitle">Recibo de transferencia</p>
            <h2 class="panel-modal-title" id="transfer-receipt-title">Transferencia exitosa</h2>
            <dl class="panel-modal-meta">
                <div>
                    <dt>Beneficiario</dt>
                    <dd id="transfer-receipt-beneficiario"></dd>
                </div>
                <div>
                    <dt>Número de cuenta destino</dt>
                    <dd id="transfer-receipt-cuenta"></dd>
                </div>
                <div>
                    <dt>Monto enviado</dt>
                    <dd id="transfer-receipt-monto"></dd>
                </div>
                <div>
                    <dt>Fecha y hora</dt>
                    <dd id="transfer-receipt-fecha"></dd>
                </div>
                <div>
                    <dt>Su descripción</dt>
                    <dd id="transfer-receipt-motivo"></dd>
                </div>
            </dl>
            <div class="panel-modal-actions">
                <button type="button" class="panel-modal-btn" id="transfer-receipt-modal-close">Finalizar</button>
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
            var boot = <?php echo json_encode($modalBootstrap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
            if (!boot) {
                return;
            }

            var svgCheck = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
            var svgWarn = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
            var svgTransfer = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>';

            if (boot.kind === 'recibo_transferencia') {
                var root = document.getElementById('transfer-receipt-modal-root');
                var backdrop = document.getElementById('transfer-receipt-modal-backdrop');
                var closeBtn = document.getElementById('transfer-receipt-modal-close');
                var iconEl = document.getElementById('transfer-receipt-modal-icon');

                function onEscKey(e) {
                    if (e.key === 'Escape' && root.classList.contains('is-open')) {
                        closeReceipt();
                    }
                }

                function closeReceipt() {
                    document.removeEventListener('keydown', onEscKey);
                    root.classList.remove('is-open');
                    root.setAttribute('aria-hidden', 'true');
                    setTimeout(function () {
                        root.setAttribute('hidden', '');
                    }, 280);
                }

                function openReceipt() {
                    root.removeAttribute('hidden');
                    root.setAttribute('aria-hidden', 'false');
                    requestAnimationFrame(function () {
                        requestAnimationFrame(function () {
                            root.classList.add('is-open');
                        });
                    });
                }

                iconEl.innerHTML = svgTransfer;
                document.getElementById('transfer-receipt-beneficiario').textContent = boot.beneficiario || '—';
                document.getElementById('transfer-receipt-cuenta').textContent = boot.numero_cuenta_destino || '—';
                document.getElementById('transfer-receipt-monto').textContent = boot.monto_formateado || '—';
                document.getElementById('transfer-receipt-fecha').textContent = boot.fecha || '—';
                var motivo = boot.descripcion_usuario;
                document.getElementById('transfer-receipt-motivo').textContent =
                    motivo && String(motivo).trim() !== '' ? motivo : '—';

                closeBtn.addEventListener('click', closeReceipt);
                backdrop.addEventListener('click', closeReceipt);
                document.addEventListener('keydown', onEscKey);

                openReceipt();
                return;
            }

            if (boot.kind !== 'panel_modal') {
                return;
            }

            var payload = boot;
            var root = document.getElementById('panel-modal-root');
            var backdrop = document.getElementById('panel-modal-backdrop');
            var iconEl = document.getElementById('panel-modal-icon');
            var titleEl = document.getElementById('panel-modal-title');
            var successBlock = document.getElementById('panel-modal-success-block');
            var errorBlock = document.getElementById('panel-modal-error-block');
            var closeBtn = document.getElementById('panel-modal-close');

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
                dialog.classList.remove('panel-modal-dialog--transfer-receipt');
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
                dialog.classList.remove('panel-modal-dialog--transfer-receipt');
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
    <script>
        (function () {
            var openBtn = document.getElementById('btn-consultar-operaciones');
            var root = document.getElementById('history-modal-root');
            if (!openBtn || !root) {
                return;
            }

            var backdrop = document.getElementById('history-modal-backdrop');
            var closeBtn = document.getElementById('history-modal-close');
            var filterBtns = root.querySelectorAll('.history-filter-btn');
            var rows = root.querySelectorAll('.history-modal-row');
            var TRANSITION_MS = 280;

            function applyFilter(filter) {
                filterBtns.forEach(function (b) {
                    b.classList.toggle('is-active', b.getAttribute('data-filter') === filter);
                });
                rows.forEach(function (row) {
                    var tipo = row.getAttribute('data-tipo') || '';
                    var show = filter === 'todos' || tipo === filter;
                    row.hidden = !show;
                });
            }

            function onHistoryEsc(e) {
                if (e.key === 'Escape' && root.classList.contains('is-open')) {
                    e.stopPropagation();
                    closeHistoryModal();
                }
            }

            function closeHistoryModal() {
                document.removeEventListener('keydown', onHistoryEsc, true);
                root.classList.remove('is-open');
                root.setAttribute('aria-hidden', 'true');
                setTimeout(function () {
                    root.setAttribute('hidden', '');
                }, TRANSITION_MS);
                openBtn.focus();
            }

            function openHistoryModal() {
                applyFilter('todos');
                root.removeAttribute('hidden');
                root.setAttribute('aria-hidden', 'false');
                document.addEventListener('keydown', onHistoryEsc, true);
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        root.classList.add('is-open');
                    });
                });
                closeBtn.focus();
            }

            openBtn.addEventListener('click', openHistoryModal);
            closeBtn.addEventListener('click', closeHistoryModal);
            backdrop.addEventListener('click', closeHistoryModal);

            filterBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var f = btn.getAttribute('data-filter');
                    if (f) {
                        applyFilter(f);
                    }
                });
            });
        })();
    </script>
</body>
</html>