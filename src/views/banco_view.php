<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Josstor</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0f172a;
            --sidebar: #1e293b;
            --card: #1e293b;
            --accent: #6366f1;
            --success: #10b981;
            --danger: #f43f5e;
            --text: #f8fafc;
            --text-muted: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }

        body { background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }

        /* Sidebar con información del Administrador */
        .sidebar { width: 280px; background: var(--sidebar); padding: 30px; border-right: 1px solid #334155; }
        .admin-box { background: rgba(99, 102, 241, 0.1); padding: 20px; border-radius: 15px; border: 1px solid var(--accent); margin-bottom: 30px; }
        .admin-box h4 { font-size: 0.8rem; color: var(--accent); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .admin-info p { font-size: 0.9rem; margin-bottom: 5px; }

        /* Contenedor Principal */
        .main { flex: 1; padding: 40px; overflow-y: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .status-badge { background: rgba(16, 185, 129, 0.2); color: var(--success); padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; }

        /* Grid de Cuentas */
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .card { background: var(--card); border-radius: 24px; padding: 30px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); position: relative; border: 1px solid #334155; }
        .card-label { color: var(--text-muted); font-size: 0.85rem; font-weight: 600; }
        .card-balance { font-size: 2.5rem; font-weight: 700; margin: 15px 0; }
        .card-meta { display: flex; justify-content: space-between; border-top: 1px solid #334155; pt-20px; padding-top: 20px; margin-top: 10px;}
        .meta-item span { display: block; font-size: 0.75rem; color: var(--text-muted); }
        .meta-item b { font-size: 1rem; }

        /* Historial de Movimientos */
        .history { background: var(--card); border-radius: 24px; padding: 30px; border: 1px solid #334155; }
        .history h3 { margin-bottom: 25px; font-size: 1.2rem; }
        .log-row { display: flex; align-items: center; padding: 15px; background: rgba(255,255,255,0.03); border-radius: 12px; margin-bottom: 10px; }
        .log-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: bold; }
        .log-data { flex: 1; }
        .log-data b { display: block; font-size: 1rem; }
        .log-data small { color: var(--text-muted); }
        .log-amount { font-weight: 700; font-size: 1.1rem; }

        .success-bg { background: rgba(16, 185, 129, 0.2); color: var(--success); }
        .danger-bg { background: rgba(244, 63, 94, 0.2); color: var(--danger); }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2 style="margin-bottom: 40px; color: var(--accent)">Bank Josstor.</h2>
        
        <div class="admin-box">
            <h4>Administrador Activo</h4>
            <div class="admin-info">
                <p><b>Nombre:</b> <?php echo htmlspecialchars($administrador->getNombres()); ?></p>
                <p><b>ID:</b> #<?php echo $administrador->getId(); ?></p>
                <p><b>Cédula:</b> <?php echo htmlspecialchars($administrador->getCedula()); ?></p>
                <p><b>Email:</b> <small><?php echo htmlspecialchars($administrador->getEmail()); ?></small></p>
            </div>
        </div>

        <nav style="color: var(--text-muted); font-size: 0.9rem;">
            <p style="margin-bottom: 15px;">Permisos detectados:</p>
            <ul style="list-style: none; padding-left: 5px;">
                <?php foreach($administrador->obtenerPermisos() as $permiso): ?>
                    <li style="margin-bottom: 8px;">✓ <?php echo str_replace('_', ' ', $permiso); ?></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </aside>

    <main class="main">
        <header class="header">
            <div>
                <h1 style="font-size: 2rem;">Cliente</h1>
                <p style="color: var(--text-muted)">Cédula: <?php echo htmlspecialchars($cliente->getCedula()); ?> | <?php echo htmlspecialchars($cliente->getEmail()); ?></p>
            </div>
            <span class="status-badge">SISTEMA ONLINE</span>
        </header>

        <section class="grid">
            <div class="card">
                <span class="card-label">CUENTA DE AHORROS PRINCIPAL</span>
                <div class="card-balance">$<?php echo number_format($cuentaOrigen->getSaldo(), 2); ?></div>
                <div class="card-meta">
                    <div class="meta-item">
                        <span>Número de Cuenta</span>
                        <b><?php echo htmlspecialchars($cuentaOrigen->getNumeroCuenta()); ?></b>
                    </div>
                    <div class="meta-item" style="text-align: right;">
                        <span>Estado</span>
                        <b style="color: var(--success)">ACTIVA</b>
                    </div>
                </div>
            </div>

            <div class="card" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                <span class="card-label">CUENTA DESTINO VINCULADA</span>
                <div class="card-balance">$<?php echo number_format($cuentaDestino->getSaldo(), 2); ?></div>
                <div class="card-meta">
                    <div class="meta-item">
                        <span>Número de Cuenta</span>
                        <b><?php echo htmlspecialchars($cuentaDestino->getNumeroCuenta()); ?></b>
                    </div>
                    <div class="meta-item" style="text-align: right;">
                        <span>Propietario ID</span>
                        <b>#<?php echo $cuentaDestino->getUsuarioId(); ?></b>
                    </div>
                </div>
            </div>
        </section>

        <section class="history">
            <h3>Trazabilidad de Movimientos</h3>
            
            <div class="log-row">
                <div class="log-icon success-bg">+</div>
                <div class="log-data">
                    <b>Depósito Recibido</b>
                    <small><?php echo htmlspecialchars($depositoMensaje); ?></small>
                </div>
                <div class="log-amount" style="color: var(--success)">+$150.00</div>
            </div>

            <div class="log-row">
                <div class="log-icon <?php echo $transferenciaExitosa ? 'success-bg' : 'danger-bg'; ?>">
                    <?php echo $transferenciaExitosa ? '⇄' : '✕'; ?>
                </div>
                <div class="log-data">
                    <b>Transferencia Bancaria</b>
                    <small><?php echo htmlspecialchars($transferenciaMensaje); ?></small>
                </div>
                <div class="log-amount" style="color: <?php echo $transferenciaExitosa ? 'var(--success)' : 'var(--danger)'; ?>">
                    $500.00
                </div>
            </div>
        </section>
    </main>
</body>
</html>