<?php

require_once __DIR__ . '/../Usuario.php';

/**
 * Representa a un cliente del sistema bancario.
 *
 * Hereda la estructura base de Usuario y define
 * permisos orientados a operaciones de consulta y movimiento.
 */
class Cliente extends Usuario
{
    /**
     * Crea un cliente con rol predefinido.
     */
    public function __construct(
        int $id,
        string $cedula,
        string $nombres,
        string $email,
        string $password,
        bool $estado = true
    ) {
        parent::__construct($id, $cedula, $nombres, $email, $password, 'cliente', $estado);
    }

    /**
     * Devuelve permisos basicos de un cliente.
     */
    public function obtenerPermisos(): array
    {
        return [
            'consultar_cuenta',
            'realizar_depositos',
            'realizar_retiros',
            'realizar_transferencias'
        ];
    }
}
