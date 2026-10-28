<?php

declare(strict_types=1);

/**
 * helpers/auth.php
 * Autenticación, registro y control de sesión.
 *
 * Conserva el mismo contrato que el antiguo AuthController:
 *   $_SESSION['usuario_id'], $_SESSION['rol'], $_SESSION['nombres']
 *   password_hash / password_verify
 *   redirecciones index.php?route=login|registro|panel
 */

use App\Models\Database;
use App\Models\Usuario;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica que exista una sesión activa.
 * Si no la hay, redirige al login.
 */
function checkAuth(): void
{
    if (empty($_SESSION['usuario_id'])) {
        header('Location: index.php?route=login');
        exit;
    }
}

function auth_login(): void
{
    if (isset($_SESSION['usuario_id'])) {
        header('Location: index.php?route=panel');
        exit;
    }

    $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $error = intentarLogin();

        if ($error === null) {
            header('Location: index.php?route=panel');
            exit;
        }
    }

    require __DIR__ . '/../src/views/login.php';
}

function auth_registro(): void
{
    if (isset($_SESSION['usuario_id'])) {
        header('Location: index.php?route=panel');
        exit;
    }

    $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $error = intentarRegistro();

        if ($error === null) {
            $_SESSION['registro_ok'] = 'Registro exitoso. Inicie sesión con su correo y contraseña.';
            header('Location: index.php?route=login');
            exit;
        }
    }

    require __DIR__ . '/../src/views/registro.php';
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            (bool) $params['secure'],
            (bool) $params['httponly']
        );
    }

    session_destroy();
    header('Location: index.php?route=login');
    exit;
}

function intentarLogin(): ?string
{
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $rolSeleccionado = (string) ($_POST['rol'] ?? '');

    if ($email === '' || $password === '') {
        return 'Ingrese correo y contraseña.';
    }

    if (!Usuario::esRolValido($rolSeleccionado)) {
        return 'Seleccione un rol válido.';
    }

    if (!Usuario::esEmailValido($email)) {
        return 'El correo electrónico no es válido.';
    }

    try {
        $db = auth_db();
    } catch (PDOException) {
        return 'No se pudo conectar a la base de datos.';
    }

    $stmt = $db->prepare(
        'SELECT id, email, password, rol, estado FROM usuarios WHERE email = :email AND rol = :rol LIMIT 1'
    );

    $stmt->execute([
        'email' => $email,
        'rol' => $rolSeleccionado,
    ]);

    $row = $stmt->fetch();
    if ($row === false || !(bool) $row['estado']) {
        return 'Credenciales incorrectas o usuario inactivo.';
    }

    if (!password_verify($password, (string) $row['password'])) {
        return 'Credenciales incorrectas o usuario inactivo.';
    }

    session_regenerate_id(true);
    $_SESSION['usuario_id'] = (int) $row['id'];
    $_SESSION['rol'] = (string) $row['rol'];
    $_SESSION['nombres'] = auth_obtenerNombreUsuario($db, (int) $row['id']);
    return null;
}

function intentarRegistro(): ?string
{
    $cedula = trim((string) ($_POST['cedula'] ?? ''));
    $nombres = trim((string) ($_POST['nombres'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $rol = (string) ($_POST['rol'] ?? '');

    if ($cedula === '' || $nombres === '' || $email === '' || $password === '') {
        return 'Complete todos los campos obligatorios.';
    }

    if (!Usuario::esRolValido($rol)) {
        return 'Seleccione un rol válido.';
    }

    if (!Usuario::esEmailValido($email)) {
        return 'El correo electrónico no es válido.';
    }

    if (!Usuario::esPasswordRegistroValida($password)) {
        return 'La contraseña debe tener al menos 6 caracteres.';
    }

    try {
        $db = auth_db();
    } catch (PDOException) {
        return 'No se pudo conectar a la base de datos.';
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $db->beginTransaction();

    try {
        $stmt = $db->prepare(
            'INSERT INTO usuarios (cedula, nombres, email, password, rol, estado) VALUES (:cedula, :nombres, :email, :password, :rol, 1)'
        );
        $stmt->execute([
            'cedula' => $cedula,
            'nombres' => $nombres,
            'email' => $email,
            'password' => $hash,
            'rol' => $rol,
        ]);
        $usuarioId = (int) $db->lastInsertId();

        if (Usuario::esRolConCuentaBancaria($rol)) {
            $tmpNumero = 'tmp-' . bin2hex(random_bytes(16));
            $insCuenta = $db->prepare(
                'INSERT INTO cuentas (usuario_id, numero_cuenta, saldo, estado) VALUES (:uid, :numero, 0.00, 1)'
            );

            $insCuenta->execute([
                'uid' => $usuarioId,
                'numero' => $tmpNumero,
            ]);

            $cuentaId = (int) $db->lastInsertId();
            $numeroFinal = sprintf('001-%06d', $cuentaId);
            $upd = $db->prepare('UPDATE cuentas SET numero_cuenta = :n WHERE id = :id LIMIT 1');
            $upd->execute(['n' => $numeroFinal, 'id' => $cuentaId]);
        }

        $db->commit();

    } catch (PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        $info = $e->errorInfo ?? [];

        if (($info[0] ?? '') === '23000' || (int) ($info[1] ?? 0) === 1062) {
            return 'Ya existe un usuario con esa cédula o correo.';
        }
        return 'No se pudo completar el registro. Intente de nuevo.';
    }
    return null;
}

function auth_obtenerNombreUsuario(PDO $db, int $id): string
{
    $stmt = $db->prepare('SELECT nombres FROM usuarios WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row !== false ? (string) $row['nombres'] : '';
}

function auth_db(): PDO
{
    static $bridge = null;

    if ($bridge === null) {
        $bridge = new class extends Database {
            public function pdo(): PDO
            {
                return $this->getConnection();
            }
        };
    }

    return $bridge->pdo();
}
