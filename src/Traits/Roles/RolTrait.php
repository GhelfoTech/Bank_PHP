<?php

declare(strict_types=1);

namespace App\Traits\Roles;

use App\Models\Usuario;

trait RolTrait
{
    protected function esRolConCuenta(string $rol): bool
    {
        return Usuario::esRolConCuentaBancaria($rol);
    }

    protected function esRolAdministrador(string $rol): bool
    {
        return $rol === 'administrador';
    }
}
