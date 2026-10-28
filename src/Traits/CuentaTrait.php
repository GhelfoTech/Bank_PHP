<?php

declare(strict_types=1);

namespace App\Traits;

trait CuentaTrait
{
    public function validarSaldo(float $monto): bool
    {
        return $this->saldo >= $monto;
    }

    protected function esMontoOperable(float $monto): bool
    {
        return $monto > 0 && $this->estado;
    }

    protected static function esNumeroCuentaValido(string $numeroCuenta): bool
    {
        return trim($numeroCuenta) !== '';
    }
}
