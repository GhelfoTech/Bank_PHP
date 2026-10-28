<?php

declare(strict_types=1);

namespace App\Models\Roles;

use App\Models\Usuario;

class Administrador extends Usuario
{
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
    
    public function obtenerPermisos(): array
    {
        return [
            'gestionar_usuarios',
            'gestionar_cuentas',
            'ver_reportes',
            'realizar_depositos',
            'realizar_retiros',
            'realizar_transferencias',
            'consultar_operaciones',
        ];
    }
}