<?php

declare(strict_types=1);

namespace App\Traits\UseCase;

use App\Models\Cuenta;
use App\Models\Roles\Administrador;
use App\Models\Roles\Cliente;
use App\Models\Usuario;
use App\Traits\Roles\RolTrait;
use PDO;

trait OperacionesTrait
{
    use RolTrait;

    protected function exigirSesionActiva(): void
    {
        checkAuth();
    }

    protected function exigirMetodoPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=panel');
            exit;
        }
    }

    /**
     * @return array{monto: float, cuenta: Cuenta}
     */
    protected function prepararOperacionClienteDesdePost(): array
    {
        $this->exigirSesionActiva();
        $this->exigirMetodoPost();

        $monto = $this->parseMontoPositivo($_POST['monto'] ?? null);

        if ($monto === null) {
            $_SESSION['panel_error'] = 'Ingrese un monto válido mayor que cero.';

            header('Location: index.php?route=panel');

            exit;
        }

        $db = $this->getConnection();

        $usuario = $this->cargarUsuarioLogueado($db);

        if (!$this->esRolConCuenta($usuario->getRol())) {
            $_SESSION['panel_error'] = 'Las operaciones bancarias solo están disponibles para usuarios con cuenta.';

            header('Location: index.php?route=panel');

            exit;
        }

        $cuentas = $this->cargarCuentasDeUsuario($db, $usuario->getId());

        if ($cuentas === []) {
            $_SESSION['panel_error'] = 'No tiene cuentas activas para operar.';

            header('Location: index.php?route=panel');

            exit;
        }

        return [
            'monto' => $monto,
            'cuenta' => $cuentas[0],
        ];
    }

    protected function cargarUsuarioLogueado(PDO $db): Usuario
    {
        $id = (int) $_SESSION['usuario_id'];
        $stmt = $db->prepare(
            'SELECT id, cedula, nombres, email, password, rol, estado
             FROM usuarios
             WHERE id = :id AND estado = 1
             LIMIT 1'
        );

        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            $_SESSION = [];
            session_destroy();

            header('Location: index.php?route=login');

            exit;
        }

        $rol = (string) $row['rol'];

        if ($rol === 'administrador') {
            return new Administrador(
                (int) $row['id'],
                (string) $row['cedula'],
                (string) $row['nombres'],
                (string) $row['email'],
                (string) $row['password'],
                (bool) $row['estado']
            );
        }

        if ($rol === 'cliente') {
            return new Cliente(
                (int) $row['id'],
                (string) $row['cedula'],
                (string) $row['nombres'],
                (string) $row['email'],
                (string) $row['password'],
                (bool) $row['estado']
            );
        }

        $_SESSION = [];
        session_destroy();
        header('Location: index.php?route=login');
        exit;
    }

    /**
     * @return list<Cuenta>
     */
    protected function cargarCuentasDeUsuario(PDO $db, int $usuarioId): array
    {
        $stmt = $db->prepare(
            'SELECT id, usuario_id, numero_cuenta, saldo, estado
             FROM cuentas
             WHERE usuario_id = :uid AND estado = 1
             ORDER BY id ASC'
        );

        $stmt->execute(['uid' => $usuarioId]);

        $cuentas = [];

        while ($row = $stmt->fetch()) {
            $cuenta = new Cuenta(
                (int) $row['id'],
                (int) $row['usuario_id'],
                (string) $row['numero_cuenta'],
                (float) $row['saldo'],
                (bool) $row['estado']
            );

            $cuenta->activarPersistencia();

            $cuentas[] = $cuenta;
        }

        return $cuentas;
    }

    protected function parseMontoPositivo(mixed $raw): ?float
    {
        if (!is_string($raw)) {
            return null;
        }

        $trim = trim($raw);

        if ($trim === '') {
            return null;
        }

        $normalized = str_replace(',', '.', $trim);

        $valor = filter_var($normalized, FILTER_VALIDATE_FLOAT);

        if ($valor === false || $valor <= 0 || !is_finite($valor)) {
            return null;
        }

        return $valor;
    }

    protected function cedulasEquivalentes(string $ingresada, string $registrada): bool
    {
        $a = strtoupper(preg_replace('/\s+/', '', trim($ingresada)));
        $b = strtoupper(preg_replace('/\s+/', '', trim($registrada)));

        if ($a === '' || $b === '') {
            return false;
        }

        if (strcasecmp($a, $b) === 0) {
            return true;
        }

        $da = preg_replace('/\D/', '', $a);
        $dbDigits = preg_replace('/\D/', '', $b);

        return $da !== '' && $da === $dbDigits;
    }

    protected function panelOkPayload(string $tipoEtiqueta, float $monto, string $descripcion): array
    {
        return [
            'tipo' => $tipoEtiqueta,
            'monto' => $monto,
            'monto_formateado' => '$' . number_format($monto, 2),
            'fecha' => date('d/m/Y H:i:s'),
            'descripcion' => $descripcion,
        ];
    }
}
