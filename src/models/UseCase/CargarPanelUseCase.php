<?php

declare(strict_types=1);

namespace App\Models\UseCase;

use App\Models\Cuenta;
use App\Models\Database;
use App\Models\Roles\Administrador;
use App\Models\Roles\Cliente;
use App\Models\Usuario;
use RuntimeException;

class CargarPanelUseCase extends Database
{
    public function execute(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        $db = $this->getConnection();

        $usuario = $this->cargarUsuarioLogueado($db);

        $cuentaOrigen = null;
        $cuentaDestino = null;
        $historial = [];

        if ($usuario->getRol() === 'cliente') {
            $cuentas = $this->cargarCuentasDeUsuario($db, $usuario->getId());

            if (count($cuentas) >= 2) {
                $cuentaOrigen = $cuentas[0];
                $cuentaDestino = $cuentas[1];
            } elseif (count($cuentas) === 1) {
                $cuentaOrigen = $cuentas[0];
            }

            $historial = Cuenta::obtenerMovimientosPorUsuarioId($usuario->getId());
        }

        require_once __DIR__ . '/../../views/banco_view.php';
    }

    private function cargarUsuarioLogueado(\PDO $db): Usuario
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

        throw new RuntimeException('Rol de usuario no reconocido.');
    }

    /**
     * @return Cuenta[]
     */
    private function cargarCuentasDeUsuario(\PDO $db, int $usuarioId): array
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
}
