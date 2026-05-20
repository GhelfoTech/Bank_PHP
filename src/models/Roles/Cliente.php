<?php

declare(strict_types=1);

namespace App\Models\Roles;

use App\Models\Usuario;

class Cliente extends Usuario
{
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

    public function obtenerPermisos(): array
    {
        return [
            'consultar_operaciones',
            'realizar_depositos',
            'realizar_retiros',
            'realizar_transferencias'
        ];
    }
}