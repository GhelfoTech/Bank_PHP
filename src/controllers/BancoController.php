<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Cuenta;
use App\Models\Database;
use App\Models\Roles\Administrador;
use App\Models\Roles\Cliente;
use App\Models\Usuario;
use PDO;
use RuntimeException;

class BancoController
{
    public function mostrarPanel(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();

        $usuario = $this->cargarUsuarioLogueado($db);

        $cuentaOrigen = null;
        $cuentaDestino = null;
        $historial = [];

        if ($usuario->getRol() === 'cliente') {
            $cuentas = $this->cargarCuentasDeUsuario($db, $usuario->getId());

            if (count($cuentas) >= 2) {
                $cuentaOrigen = $cuentas[0];
                $cuentaDestino = $cuentas[1];
            } elseif (count($cuentas) === 1) {
                $cuentaOrigen = $cuentas[0];
            }

            $historial = Cuenta::obtenerMovimientosPorUsuarioId($db, $usuario->getId());
        }

        require_once __DIR__ . '/../views/banco_view.php';
    }

    private function cargarUsuarioLogueado(PDO $db): Usuario
    {
        $id = (int) $_SESSION['usuario_id'];

        $stmt = $db->prepare(
            'SELECT id, cedula, nombres, email, password, rol, estado
             FROM usuarios
             WHERE id = :id AND estado = 1
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            $_SESSION = [];
            session_destroy();
            header('Location: index.php?route=login');
            exit;
        }

        $rol = (string) $row['rol'];

        if ($rol === 'administrador') {
            return new Administrador(
                (int) $row['id'],
                (string) $row['cedula'],
                (string) $row['nombres'],
                (string) $row['email'],
                (string) $row['password'],
                (bool) $row['estado'],
                $db
            );
        }

        if ($rol === 'cliente') {
            return new Cliente(
                (int) $row['id'],
                (string) $row['cedula'],
                (string) $row['nombres'],
                (string) $row['email'],
                (string) $row['password'],
                (bool) $row['estado'],
                $db
            );
        }

        throw new RuntimeException('Rol de usuario no reconocido.');
    }

    /**
     * @return Cuenta[]
     */
    private function cargarCuentasDeUsuario(PDO $db, int $usuarioId): array
    {
        $stmt = $db->prepare(
            'SELECT id, usuario_id, numero_cuenta, saldo, estado
             FROM cuentas
             WHERE usuario_id = :uid AND estado = 1
             ORDER BY id ASC'
        );
        $stmt->execute(['uid' => $usuarioId]);

        $cuentas = [];
        while ($row = $stmt->fetch()) {
            $cuentas[] = new Cuenta(
                (int) $row['id'],
                (int) $row['usuario_id'],
                (string) $row['numero_cuenta'],
                (float) $row['saldo'],
                (bool) $row['estado'],
                $db
            );
        }

        return $cuentas;
    }

    public function procesarDeposito(): void
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

    public function procesarRetiro(): void
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

    public function procesarTransferencia(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=panel');
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        $usuario = $this->cargarUsuarioLogueado($db);

        if ($usuario->getRol() !== 'cliente') {
            $_SESSION['panel_error'] = 'Las transferencias solo están disponibles para clientes con cuenta.';
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

        $destinoInfo = Cuenta::obtenerCuentaActivaConTitularPorNumero($db, $numeroDestino);
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

    /**
     * @return array{monto: float, cuenta: Cuenta}
     */
    private function prepararOperacionClienteDesdePost(): array
    {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=panel');
            exit;
        }

        $monto = $this->parseMontoPositivo($_POST['monto'] ?? null);
        if ($monto === null) {
            $_SESSION['panel_error'] = 'Ingrese un monto válido mayor que cero.';
            header('Location: index.php?route=panel');
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();

        $usuario = $this->cargarUsuarioLogueado($db);
        if ($usuario->getRol() !== 'cliente') {
            $_SESSION['panel_error'] = 'Las operaciones bancarias solo están disponibles para clientes con cuenta.';
            header('Location: index.php?route=panel');
            exit;
        }

        $cuentas = $this->cargarCuentasDeUsuario($db, $usuario->getId());
        if ($cuentas === []) {
            $_SESSION['panel_error'] = 'No tiene cuentas activas para operar.';
            header('Location: index.php?route=panel');
            exit;
        }

        return [
            'monto' => $monto,
            'cuenta' => $cuentas[0],
        ];
    }

    private function parseMontoPositivo(mixed $raw): ?float
    {
        if (!is_string($raw)) {
            return null;
        }

        $trim = trim($raw);
        if ($trim === '') {
            return null;
        }

        $normalized = str_replace(',', '.', $trim);
        $valor = filter_var($normalized, FILTER_VALIDATE_FLOAT);

        if ($valor === false || $valor <= 0 || !is_finite($valor)) {
            return null;
        }

        return $valor;
    }

    /**
     * Datos para el modal de éxito del panel (tras redirect).
     *
     * @return array{tipo: string, monto: float, monto_formateado: string, fecha: string, descripcion: string}
     */
    private function panelOkPayload(string $tipoEtiqueta, float $monto, string $descripcion): array
    {
        return [
            'tipo' => $tipoEtiqueta,
            'monto' => $monto,
            'monto_formateado' => '$' . number_format($monto, 2),
            'fecha' => date('d/m/Y H:i:s'),
            'descripcion' => $descripcion,
        ];
    }

    private function cedulasEquivalentes(string $ingresada, string $registrada): bool
    {
        $a = strtoupper(preg_replace('/\s+/', '', trim($ingresada)));
        $b = strtoupper(preg_replace('/\s+/', '', trim($registrada)));

        if ($a === '' || $b === '') {
            return false;
        }

        if (strcasecmp($a, $b) === 0) {
            return true;
        }

        $da = preg_replace('/\D/', '', $a);
        $db = preg_replace('/\D/', '', $b);

        return $da !== '' && $da === $db;
    }
}
