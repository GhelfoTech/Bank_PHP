<?php

declare(strict_types=1);

namespace App\Models\UseCase;

use App\Models\Cuenta;
use App\Models\Database;
use App\Models\Roles\Administrador;
use App\Models\Roles\Cliente;
use App\Models\Usuario;
use App\Traits\UseCase\OperacionesTrait;
use RuntimeException;

class CargarPanelUseCase extends Database
{
    use OperacionesTrait;

    public function execute(): void
    {
        $this->exigirSesionActiva();

        $db = $this->getConnection();
        $usuario = $this->cargarUsuarioLogueado($db);

        $cuentaOrigen = null;
        $cuentaDestino = null;
        $historial = [];
        $usuariosGestion = [];

        $rolActual = $usuario->getRol();
        $esAdministrador = $this->esRolAdministrador($rolActual);
        $puedeOperarFinancieramente = $this->esRolConCuenta($rolActual);

        if ($puedeOperarFinancieramente) {
            $cuentas = $this->cargarCuentasDeUsuario($db, $usuario->getId());

            if (count($cuentas) >= 2) {
                $cuentaOrigen = $cuentas[0];
                $cuentaDestino = $cuentas[1];
            } elseif (count($cuentas) === 1) {
                $cuentaOrigen = $cuentas[0];
            }

            $historial = Cuenta::obtenerMovimientosPorUsuarioId($usuario->getId());
        }

        if ($esAdministrador) {
            $usuariosGestion = $this->listarUsuariosParaGestion($db, $usuario->getId());
        }

        require_once __DIR__ . '/../../views/banco_view.php';
    }

    /**
     * @return list<array{
     *     id:int,
     *     cedula:string,
     *     nombres:string,
     *     email:string,
     *     rol:string,
     *     estado:bool,
     *     cuenta_id:?int,
     *     numero_cuenta:?string,
     *     saldo:?float,
     *     cuenta_estado:?bool
     * }>
     */
    private function listarUsuariosParaGestion(\PDO $db, int $adminId): array
    {
        $stmt = $db->prepare(
            'SELECT u.id, u.cedula, u.nombres, u.email, u.rol, u.estado,
                    c.id AS cuenta_id, c.numero_cuenta, c.saldo, c.estado AS cuenta_estado
             FROM usuarios u
             LEFT JOIN cuentas c
               ON c.id = (
                   SELECT MIN(c2.id)
                   FROM cuentas c2
                   WHERE c2.usuario_id = u.id
               )
             WHERE u.id <> :admin_id
             ORDER BY u.id ASC'
        );
        $stmt->execute(['admin_id' => $adminId]);

        $lista = [];
        while ($row = $stmt->fetch()) {
            $lista[] = [
                'id' => (int) $row['id'],
                'cedula' => (string) $row['cedula'],
                'nombres' => (string) $row['nombres'],
                'email' => (string) $row['email'],
                'rol' => (string) $row['rol'],
                'estado' => (bool) $row['estado'],
                'cuenta_id' => $row['cuenta_id'] !== null ? (int) $row['cuenta_id'] : null,
                'numero_cuenta' => $row['numero_cuenta'] !== null ? (string) $row['numero_cuenta'] : null,
                'saldo' => $row['saldo'] !== null ? (float) $row['saldo'] : null,
                'cuenta_estado' => $row['cuenta_estado'] !== null ? (bool) $row['cuenta_estado'] : null,
            ];
        }

        return $lista;
    }
}