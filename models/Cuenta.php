<?php

require_once __DIR__ . '/Movimiento.php';

/**
 * Representa una cuenta bancaria vinculada a un usuario.
 *
 * Contiene la logica de negocio basica para depositar, retirar y transferir,
 * y registra los movimientos en memoria como base para futura persistencia.
 */
class Cuenta
{
    private int $id;
    private int $usuario_id;
    private string $numero_cuenta;
    private float $saldo;
    private bool $estado;

    /**
     * @var Movimiento[]
     */
    private array $movimientos = [];

    /**
     * Inicializa la cuenta con sus datos principales.
     */
    public function __construct(
        int $id,
        int $usuario_id,
        string $numero_cuenta,
        float $saldo = 0.0,
        bool $estado = true
    ) {
        $this->id = $id;
        $this->usuario_id = $usuario_id;
        $this->numero_cuenta = $numero_cuenta;
        $this->saldo = $saldo;
        $this->estado = $estado;
    }

    /**
     * Verifica si la cuenta posee saldo suficiente para un monto dado.
     */
    public function validarSaldo(float $monto): bool
    {
        return $this->saldo >= $monto;
    }

    /**
     * Realiza un deposito y registra el movimiento en memoria.
     */
    public function depositar(float $monto): bool
    {
        if ($monto <= 0 || !$this->estado) {
            return false;
        }

        $this->saldo += $monto;
        $this->registrarMovimiento('deposito', $monto, 'Deposito en cuenta');

        return true;
    }

    /**
     * Realiza un retiro si existe saldo suficiente y la cuenta esta activa.
     */
    public function retirar(float $monto): bool
    {
        if ($monto <= 0 || !$this->estado || !$this->validarSaldo($monto)) {
            return false;
        }

        $this->saldo -= $monto;
        $this->registrarMovimiento('retiro', $monto, 'Retiro en cuenta');

        return true;
    }

    /**
     * Transfiere fondos a otra cuenta.
     *
     * IMPORTANTE: en una implementacion real con base de datos, este proceso
     * debe ejecutarse dentro de una Transaccion Obligatoria para garantizar
     * atomicidad (debito y credito exitosos o rollback completo).
     */
    public function transferir(Cuenta $destino, float $monto): bool
    {
        if ($monto <= 0 || !$this->estado || !$destino->getEstado()) {
            return false;
        }

        if (!$this->validarSaldo($monto)) {
            return false;
        }

        $this->saldo -= $monto;
        $destino->saldo += $monto;

        $this->registrarMovimiento(
            'transferencia',
            $monto,
            'Transferencia enviada a ' . $destino->getNumeroCuenta()
        );

        $destino->registrarMovimiento(
            'transferencia',
            $monto,
            'Transferencia recibida desde ' . $this->numero_cuenta
        );

        return true;
    }

    /**
     * Registra un movimiento simple en el historial en memoria.
     */
    private function registrarMovimiento(string $tipo, float $monto, string $descripcion): void
    {
        $idMovimiento = count($this->movimientos) + 1;
        $fecha = date('Y-m-d H:i:s');

        $this->movimientos[] = new Movimiento(
            $idMovimiento,
            $this->id,
            $tipo,
            $monto,
            $fecha,
            $descripcion
        );
    }

    /**
     * Obtiene el identificador de la cuenta.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Define el identificador de la cuenta.
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Obtiene el id del usuario propietario.
     */
    public function getUsuarioId(): int
    {
        return $this->usuario_id;
    }

    /**
     * Define el id del usuario propietario.
     */
    public function setUsuarioId(int $usuario_id): void
    {
        $this->usuario_id = $usuario_id;
    }

    /**
     * Obtiene el numero de cuenta.
     */
    public function getNumeroCuenta(): string
    {
        return $this->numero_cuenta;
    }

    /**
     * Define el numero de cuenta.
     */
    public function setNumeroCuenta(string $numero_cuenta): void
    {
        $this->numero_cuenta = $numero_cuenta;
    }

    /**
     * Obtiene el saldo actual.
     */
    public function getSaldo(): float
    {
        return $this->saldo;
    }

    /**
     * Define el saldo de la cuenta.
     */
    public function setSaldo(float $saldo): void
    {
        $this->saldo = $saldo;
    }

    /**
     * Indica si la cuenta esta activa.
     */
    public function getEstado(): bool
    {
        return $this->estado;
    }

    /**
     * Define el estado activo/inactivo de la cuenta.
     */
    public function setEstado(bool $estado): void
    {
        $this->estado = $estado;
    }

    /**
     * Devuelve el historial de movimientos de la cuenta.
     *
     * @return Movimiento[]
     */
    public function getMovimientos(): array
    {
        return $this->movimientos;
    }
}
