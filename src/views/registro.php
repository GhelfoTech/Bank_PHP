<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro — Bank Josstor</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/styles.css">
    <style>
        .auth-wrap { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px 20px; min-height: 100vh; }
        .auth-card { width: 100%; max-width: 460px; background: var(--card); border: 1px solid #334155; border-radius: 16px; padding: 32px; }
        .auth-card h1 { font-size: 1.35rem; margin-bottom: 8px; }
        .auth-card p.sub { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 24px; }
        .auth-field { margin-bottom: 16px; }
        .auth-field label { display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 6px; }
        .auth-field input, .auth-field select { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #334155; background: #0f172a; color: var(--text); font-size: 0.95rem; box-sizing: border-box; }
        .auth-field input:focus, .auth-field select:focus { outline: none; border-color: var(--accent); }
        .auth-password-wrap { position: relative; width: 100%; }
        .auth-password-wrap input { padding-right: 4.25rem; }
        .auth-password-toggle {
            position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
            border: none; background: transparent; color: var(--text-muted);
            font-size: 0.75rem; font-weight: 600; font-family: inherit; cursor: pointer;
            padding: 6px 8px; border-radius: 6px; line-height: 1.2;
        }
        .auth-password-toggle:hover { color: var(--accent); background: rgba(148, 163, 184, 0.12); }
        .auth-password-toggle:focus { outline: none; color: var(--accent); box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.35); }
        .auth-btn { width: 100%; padding: 12px; border: none; border-radius: 10px; background: var(--accent); color: var(--white); font-weight: 600; cursor: pointer; margin-top: 8px; }
        .auth-btn:hover { filter: brightness(1.08); }
        .auth-links { margin-top: 20px; text-align: center; font-size: 0.9rem; color: var(--text-muted); }
        .auth-links a { color: var(--accent); text-decoration: none; }
        .auth-links a:hover { text-decoration: underline; }
        .msg-err { background: rgba(244, 63, 94, 0.12); border: 1px solid var(--danger); color: var(--danger); padding: 12px; border-radius: 8px; margin-bottom: 18px; font-size: 0.9rem; }
        .hint { font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; }
    </style>
</head>
<body>
    <div class="auth-wrap">
        <div class="auth-card">
            <h1>Crear cuenta</h1>

            <?php if ($error !== null): ?>
                <div class="msg-err"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="post" action="index.php?route=registro" autocomplete="on">
                <div class="auth-field">
                    <label for="cedula">Cédula</label>
                    <input type="text" id="cedula" name="cedula" required maxlength="20"
                           placeholder="V-12345678"
                           value="<?php echo htmlspecialchars((string) ($_POST['cedula'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="auth-field">
                    <label for="nombres">Nombre completo</label>
                    <input type="text" id="nombres" name="nombres" required maxlength="120"
                           placeholder="Juan Pérez"
                           value="<?php echo htmlspecialchars((string) ($_POST['nombres'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="auth-field">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" required maxlength="120"
                           placeholder="ejemplocorreo@gmail.com"
                           value="<?php echo htmlspecialchars((string) ($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="auth-field">
                    <label for="password">Contraseña</label>
                    <div class="auth-password-wrap">
                        <input type="password" id="password" name="password" required minlength="6" autocomplete="new-password"
                               placeholder="••••••••">
                        <button type="button" class="auth-password-toggle" data-target="password" aria-pressed="false" aria-label="Mostrar contraseña">Ver</button>
                    </div>
                    <p class="hint">Mínimo 6 caracteres.</p>
                </div>
                <div class="auth-field">
                    <label for="rol">Rol</label>
                    <select id="rol" name="rol" required title="Seleccione su rol">
                        <option value="" hidden disabled <?php echo empty($_POST['rol'] ?? '') ? 'selected' : ''; ?>>Seleccione su rol</option>
                        <option value="cliente" <?php echo (($_POST['rol'] ?? '') === 'cliente') ? 'selected' : ''; ?>>Cliente</option>
                        <option value="administrador" <?php echo (($_POST['rol'] ?? '') === 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                    </select>
                </div>
                <button type="submit" class="auth-btn">Registrarse</button>
            </form>

            <div class="auth-links">
                ¿Ya tiene cuenta? <a href="index.php?route=login">Iniciar sesión</a>
            </div>
        </div>
    </div>
    <script>
        (function () {
            document.querySelectorAll('.auth-password-toggle').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var id = btn.getAttribute('data-target');
                    var input = id ? document.getElementById(id) : null;
                    if (!input) return;
                    var visible = input.getAttribute('type') === 'text';
                    input.setAttribute('type', visible ? 'password' : 'text');
                    btn.setAttribute('aria-pressed', visible ? 'false' : 'true');
                    btn.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
                    btn.textContent = visible ? 'Ver' : 'Ocultar';
                });
            });
        })();
    </script>
</body>
</html>
