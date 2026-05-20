<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UseCase\CargarPanelUseCase;
use App\Models\UseCase\ProcesarDepositoUseCase;
use App\Models\UseCase\ProcesarRetiroUseCase;
use App\Models\UseCase\ProcesarTransferenciaUseCase;

class BancoController
{
    public function mostrarPanel(): void
    {
        (new CargarPanelUseCase())->execute();
    }

    public function procesarDeposito(): void
    {
        (new ProcesarDepositoUseCase())->execute();
    }

    public function procesarRetiro(): void
    {
        (new ProcesarRetiroUseCase())->execute();
    }
    
    public function procesarTransferencia(): void
    {
        (new ProcesarTransferenciaUseCase())->execute();
    }
}