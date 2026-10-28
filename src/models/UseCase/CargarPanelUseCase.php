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
        $usuariosGestion = [];

        if (in_array($usuario->getRol(), ['cliente', 'administrador'], true)) {
            $cuentas = $this->cargarCuentasDeUsuario($db, $usuario->getId());
            
            if (count($cuentas) >= 2) {
                $cuentaOrigen = $cuentas[0];
                $cuentaDestino = $cuentas[1];
            } 
            elseif (count($cuentas) === 1) {
                $cuentaOrigen = $cuentas[0];
            }
            
            $historial = Cuenta::obtenerMovimientosPorUsuarioId($usuario->getId());
        }

        if ($usuario->getRol() === 'administrador') {
            $usuariosGestion = $this->listarUsuariosParaGestion($db, $usuario->getId());
        }

        require_once __DIR__ . '/../../views/banco_view.php';
    }

    private function cargarUsuarioLogueado(\PDO $db): Usuario
    {
        $id = (int) $_SESSION['usuario_id'];
        $stmt = $db->prepare('SELECT id, cedula, nombres, email, 
                              password, rol, estado 
                              FROM usuarios 
                              WHERE id = :id 
                              AND estado = 1 
                              LIMIT 1');

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
            return new Administrador((int) $row['id'], 
                                     (string) $row['cedula'], 
                                     (string) $row['nombres'], 
                                     (string) $row['email'], 
                                     (string) $row['password'], 
                                     (bool) $row['estado']);
        }

        if ($rol === 'cliente') {
            return new Cliente((int) $row['id'], 
                               (string) $row['cedula'], 
                               (string) $row['nombres'], 
                               (string) $row['email'], 
                               (string) $row['password'], 
                               (bool) $row['estado']);
        }
        
        throw new RuntimeException('Rol de usuario no reconocido.');
    }
    
    private function cargarCuentasDeUsuario(\PDO $db, int $usuarioId): array
    {
        $stmt = $db->prepare('SELECT id, usuario_id, numero_cuenta, saldo, estado 
                              FROM cuentas 
                              WHERE usuario_id = :uid 
                              AND estado = 1 
                              ORDER BY id ASC');

        $stmt->execute(['uid' => $usuarioId]);
        $cuentas = [];
        
        while ($row = $stmt->fetch()) {
            $cuenta = new Cuenta((int) $row['id'], 
                                 (int) $row['usuario_id'], 
                                 (string) $row['numero_cuenta'], 
                                 (float) $row['saldo'], 
                                 (bool) $row['estado']);

            $cuenta->activarPersistencia();
            $cuentas[] = $cuenta;
        }
        
        return $cuentas;
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