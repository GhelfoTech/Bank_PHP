<?php

declare(strict_types=1);

namespace App\Traits;

trait UsuarioTrait
{
    /**
     * @return list<string>
     */
    public static function rolesPermitidos(): array
    {
        return ['administrador', 'cliente'];
    }

    public static function esRolValido(string $rol): bool
    {
        return in_array($rol, self::rolesPermitidos(), true);
    }

    public static function esEmailValido(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function esPasswordRegistroValida(string $password): bool
    {
        return strlen($password) >= 6;
    }

    public static function esRolConCuentaBancaria(string $rol): bool
    {
        return in_array($rol, ['cliente', 'administrador'], true);
    }
}
