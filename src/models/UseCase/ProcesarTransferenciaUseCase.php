<?php

declare(strict_types=1);

namespace App\Models\UseCase;

use App\Models\Cuenta;
use App\Models\Database;
use App\Traits\UseCase\OperacionesTrait;

class ProcesarTransferenciaUseCase extends Database
{
    use OperacionesTrait;

    public function execute(): void
    {
        $this->exigirSesionActiva();
        $this->exigirMetodoPost();

        $db = $this->getConnection();

        $usuario = $this->cargarUsuarioLogueado($db);

        if (!$this->esRolConCuenta($usuario->getRol())) {
            $_SESSION['panel_error'] = 'Las transferencias solo están disponibles para usuarios con cuenta.';

            header('Location: index.php?route=panel');

            exit;
        }

        $numeroDestino = trim((string) ($_POST['numero_cuenta_destino'] ?? ''));
        $cedulaDestino = trim((string) ($_POST['cedula_destino'] ?? ''));
        $descripcionPersonalizada = trim((string) ($_POST['descripcion_personalizada'] ?? ''));

        if ($numeroDestino === '' || $cedulaDestino === '') {
            $_SESSION['panel_error'] = 'Indique el número de cuenta destino y la cédula del titular.';

            header('Location: index.php?route=panel');

            exit;
        }

        $monto = $this->parseMontoPositivo($_POST['monto'] ?? null);

        if ($monto === null) {
            $_SESSION['panel_error'] = 'Ingrese un monto válido mayor que cero.';

            header('Location: index.php?route=panel');

            exit;
        }

        $cuentas = $this->cargarCuentasDeUsuario($db, $usuario->getId());

        if ($cuentas === []) {
            $_SESSION['panel_error'] = 'No tiene cuentas activas para operar.';

            header('Location: index.php?route=panel');

            exit;
        }

        $cuentaOrigen = $cuentas[0];

        $destinoInfo = Cuenta::obtenerCuentaActivaConTitularPorNumero($numeroDestino);

        if ($destinoInfo === null) {
            $_SESSION['panel_error'] = 'No se encontró una cuenta activa con el número indicado.';

            header('Location: index.php?route=panel');

            exit;
        }

        $cuentaDestino = $destinoInfo['cuenta'];
        $titularCedula = (string) $destinoInfo['titular_cedula'];
        $titularNombres = (string) $destinoInfo['titular_nombres'];

        if (!$this->cedulasEquivalentes($cedulaDestino, $titularCedula)) {
            $_SESSION['panel_error'] = 'Los datos de la cuenta no coinciden con la cédula proporcionada';

            header('Location: index.php?route=panel');

            exit;
        }

        if ($cuentaOrigen->getId() === $cuentaDestino->getId()) {
            $_SESSION['panel_error'] = 'No puede transferir fondos a su propia cuenta.';

            header('Location: index.php?route=panel');

            exit;
        }

        if (!$cuentaOrigen->validarSaldo($monto)) {
            $_SESSION['panel_error'] = 'Saldo insuficiente para realizar la transferencia.';

            header('Location: index.php?route=panel');

            exit;
        }

        $ok = $cuentaOrigen->transferir(
            $cuentaDestino,
            $monto,
            $descripcionPersonalizada,
            $usuario->getNombres(),
            $titularNombres
        );

        if (!$ok) {
            $_SESSION['panel_error'] = 'No se pudo completar la transferencia. Intente nuevamente.';

            header('Location: index.php?route=panel');

            exit;
        }

        $_SESSION['panel_ok'] = [
            'modal' => 'recibo_transferencia',
            'beneficiario' => $titularNombres,
            'numero_cuenta_destino' => $cuentaDestino->getNumeroCuenta(),
            'monto' => $monto,
            'monto_formateado' => '$' . number_format($monto, 2),
            'fecha' => date('d/m/Y H:i:s'),
            'descripcion_usuario' => $descripcionPersonalizada,
        ];

        header('Location: index.php?route=panel');

        exit;
    }
}
