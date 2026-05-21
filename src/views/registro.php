<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro — Bank Josstor</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/styles.css">
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