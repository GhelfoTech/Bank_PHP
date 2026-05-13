<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — Bank Josstor</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/styles.css">
    <style>
        .auth-wrap { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px 20px; min-height: 100vh; }
        .auth-card { width: 100%; max-width: 420px; background: var(--card); border: 1px solid #334155; border-radius: 16px; padding: 32px; }
        .auth-card h1 { font-size: 1.35rem; margin-bottom: 8px; }
        .auth-card p.sub { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 24px; }
        .auth-field { margin-bottom: 18px; }
        .auth-field label { display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 6px; }
        .auth-field input, .auth-field select { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #334155; background: #0f172a; color: var(--text); font-size: 0.95rem; }
        .auth-field input:focus, .auth-field select:focus { outline: none; border-color: var(--accent); }
        .auth-btn { width: 100%; padding: 12px; border: none; border-radius: 10px; background: var(--accent); color: var(--white); font-weight: 600; cursor: pointer; margin-top: 8px; }
        .auth-btn:hover { filter: brightness(1.08); }
        .auth-links { margin-top: 20px; text-align: center; font-size: 0.9rem; color: var(--text-muted); }
        .auth-links a { color: var(--accent); text-decoration: none; }
        .auth-links a:hover { text-decoration: underline; }
        .msg-ok { background: rgba(16, 185, 129, 0.15); border: 1px solid var(--success); color: var(--success); padding: 12px; border-radius: 8px; margin-bottom: 18px; font-size: 0.9rem; }
        .msg-err { background: rgba(244, 63, 94, 0.12); border: 1px solid var(--danger); color: var(--danger); padding: 12px; border-radius: 8px; margin-bottom: 18px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="auth-wrap">
        <div class="auth-card">
            <h1>Bank Josstor</h1>
            <p class="sub">Inicie sesión con su correo, contraseña y rol.</p>

            <?php if (!empty($_SESSION['registro_ok'])): ?>
                <div class="msg-ok"><?php echo htmlspecialchars((string) $_SESSION['registro_ok'], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php unset($_SESSION['registro_ok']); ?>
            <?php endif; ?>

            <?php if ($error !== null): ?>
                <div class="msg-err"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="post" action="index.php?route=login" autocomplete="on">
                <div class="auth-field">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" required maxlength="120"
                           value="<?php echo htmlspecialchars((string) ($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="auth-field">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required minlength="6" autocomplete="current-password">
                </div>
                <div class="auth-field">
                    <label for="rol">Rol</label>
                    <select id="rol" name="rol" required>
                        <option value="">— Seleccione —</option>
                        <option value="cliente" <?php echo (($_POST['rol'] ?? '') === 'cliente') ? 'selected' : ''; ?>>Cliente</option>
                        <option value="administrador" <?php echo (($_POST['rol'] ?? '') === 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                    </select>
                </div>
                <button type="submit" class="auth-btn">Entrar</button>
            </form>

            <div class="auth-links">
                ¿No tiene cuenta? <a href="index.php?route=registro">Registrarse</a>
            </div>
        </div>
    </div>
</body>
</html>
