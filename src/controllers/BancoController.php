<?php

namespace App\Controllers;

use App\Models\Cuenta;
use App\Models\Roles\Administrador;
use App\Models\Roles\Cliente;

class BancoController {
    public function mostrarPanel() {
        $administrador = new Administrador(1, '0102030405', 'Ana Admin', 'ana.admin@banco.local', 'admin123');
        $cliente = new Cliente(2, '1919191919', 'Carlos Cliente', 'carlos.cliente@banco.local', 'cliente123');
        
        $cuentaOrigen = new Cuenta(1, $cliente->getId(), '001-000001', 400.00, true);
        $cuentaDestino = new Cuenta(2, $cliente->getId(), '001-000002', 50.00, true);


        $depositoExitoso = $cuentaOrigen->depositar(150.00);
        $transferenciaExitosa = $cuentaOrigen->transferir($cuentaDestino, 500.00);


        $depositoEstado = $depositoExitoso ? 'Exitoso' : 'Fallido';
        $transferenciaEstado = $transferenciaExitosa ? 'Exitoso' : 'Fallido';
        $depositoMensaje = "Operacion de deposito: {$depositoEstado}";
        $transferenciaMensaje = "Operacion de transferencia: {$transferenciaEstado}";

        require_once __DIR__ . '/../views/banco_view.php';
    }
}