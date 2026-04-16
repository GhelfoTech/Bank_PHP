<?php

namespace App\Models\Roles;

use App\Models\Usuario;

/**
 * Representa a un administrador del sistema bancario.
 *
 * Hereda la estructura de Usuario y amplifica los permisos
 * para administracion de usuarios, cuentas y reportes.
 */
class Administrador extends Usuario
{
    /**
     * Crea un administrador con rol predefinido.
     */
    public function __construct(
        int $id,
        string $cedula,
        string $nombres,
        string $email,
        string $password,
        bool $estado = true
    ) {
        parent::__construct($id, $cedula, $nombres, $email, $password, 'administrador', $estado);
    }

    /**
     * Devuelve permisos avanzados de un administrador.
     */
    public function obtenerPermisos(): array
    {
        return [
            'gestionar_usuarios',
            'gestionar_cuentas',
            'aprobar_operaciones',
            'ver_reportes'
        ];
    }
}
