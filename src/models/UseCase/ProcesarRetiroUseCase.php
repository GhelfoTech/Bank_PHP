<?php

declare(strict_types=1);

namespace App\Models\UseCase;

use App\Models\Database;
use App\Traits\UseCase\OperacionesTrait;

class ProcesarRetiroUseCase extends Database
{
    use OperacionesTrait;

    public function execute(): void
    {
        $ctx = $this->prepararOperacionClienteDesdePost();

        $monto = $ctx['monto'];
        $cuenta = $ctx['cuenta'];

        if (!$cuenta->validarSaldo($monto)) {
            $_SESSION['panel_error'] = 'Saldo insuficiente para realizar el retiro.';

            header('Location: index.php?route=panel');

            exit;
        }

        if (!$cuenta->retirar($monto)) {
            $_SESSION['panel_error'] = 'No se pudo completar el retiro. Intente nuevamente.';
        } else {
            $_SESSION['panel_ok'] = $this->panelOkPayload(
                'Retiro',
                 $monto,
                'El monto se debitó correctamente de su cuenta principal.'
            );
        }

        header('Location: index.php?route=panel');

        exit;
    }
}
