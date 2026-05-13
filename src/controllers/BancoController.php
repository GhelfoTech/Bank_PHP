<?php

namespace App\Controllers;

use App\Models\Cuenta;
use App\Models\Database;
use App\Models\Roles\Administrador;
use App\Models\Roles\Cliente;
use PDO;
use RuntimeException;

class BancoController
{
    public function mostrarPanel(): void
    {
        $database = new Database();
        $db = $database->getConnection();

        $administrador = $this->cargarAdministrador($db);
        $cliente = $this->cargarCliente($db);

        $cuentaOrigen = $this->cargarCuentaPorNumero($db, '001-000001');
        $cuentaDestino = $this->cargarCuentaPorNumero($db, '001-000002');

        $depositoExitoso = $cuentaOrigen->depositar(150.00);
        $transferenciaExitosa = $cuentaOrigen->transferir($cuentaDestino, 500.00);

        // Persistencia en base de datos ya aplicada arriba; cualquier notificacion WebSocket
        // deberia emitirse solo despues de operaciones exitosas (integracion futura).

        $depositoEstado = $depositoExitoso ? 'Exitoso' : 'Fallido';
        $transferenciaEstado = $transferenciaExitosa ? 'Exitoso' : 'Fallido';
        $depositoMensaje = "Operacion de deposito: {$depositoEstado}";
        $transferenciaMensaje = "Operacion de transferencia: {$transferenciaEstado}";

        require_once __DIR__ . '/../views/banco_view.php';
    }

    private function cargarAdministrador(PDO $db): Administrador
    {
        $stmt = $db->prepare(
            'SELECT id, cedula, nombres, email, password, estado FROM usuarios WHERE id = :id AND rol = :rol LIMIT 1'
        );
        $stmt->execute([
            'id' => 1,
            'rol' => 'administrador',
        ]);
        $row = $stmt->fetch();

        if ($row === false) {
            throw new RuntimeException(
                'No se encontro el administrador (id=1). Cree la base de datos e importe database/schema.sql.'
            );
        }

        return new Administrador(
            (int) $row['id'],
            (string) $row['cedula'],
            (string) $row['nombres'],
            (string) $row['email'],
            (string) $row['password'],
            (bool) $row['estado'],
            $db
        );
    }

    private function cargarCliente(PDO $db): Cliente
    {
        $stmt = $db->prepare(
            'SELECT id, cedula, nombres, email, password, estado FROM usuarios WHERE id = :id AND rol = :rol LIMIT 1'
        );
        $stmt->execute([
            'id' => 2,
            'rol' => 'cliente',
        ]);
        $row = $stmt->fetch();

        if ($row === false) {
            throw new RuntimeException(
                'No se encontro el cliente (id=2). Cree la base de datos e importe database/schema.sql.'
            );
        }

        return new Cliente(
            (int) $row['id'],
            (string) $row['cedula'],
            (string) $row['nombres'],
            (string) $row['email'],
            (string) $row['password'],
            (bool) $row['estado'],
            $db
        );
    }

    private function cargarCuentaPorNumero(PDO $db, string $numeroCuenta): Cuenta
    {
        $stmt = $db->prepare(
            'SELECT id, usuario_id, numero_cuenta, saldo, estado FROM cuentas WHERE numero_cuenta = :numero LIMIT 1'
        );
        $stmt->execute(['numero' => $numeroCuenta]);
        $row = $stmt->fetch();

        if ($row === false) {
            throw new RuntimeException(
                "No se encontro la cuenta {$numeroCuenta}. Importe database/schema.sql o cree el registro."
            );
        }

        return new Cuenta(
            (int) $row['id'],
            (int) $row['usuario_id'],
            (string) $row['numero_cuenta'],
            (float) $row['saldo'],
            (bool) $row['estado'],
            $db
        );
    }
}
