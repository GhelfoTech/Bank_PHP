<?php

declare(strict_types=1);

namespace App\Models\UseCase;

use App\Models\Database;
use App\Traits\UseCase\OperacionesTrait;

class ProcesarDepositoUseCase extends Database
{
    use OperacionesTrait;

    public function execute(): void
    {
        $ctx = $this->prepararOperacionClienteDesdePost();
        $monto = $ctx['monto'];
        $cuenta = $ctx['cuenta'];

        if ($cuenta->depositar($monto)) {
            $_SESSION['panel_ok'] = $this->panelOkPayload(
                'Depósito',
                 $monto,
                'Los fondos se acreditaron correctamente en su cuenta principal.'
            );
        } else {
            $_SESSION['panel_error'] = 'No se pudo completar el depósito. Intente nuevamente.';
        }

        header('Location: index.php?route=panel');

        exit;
    }
}
