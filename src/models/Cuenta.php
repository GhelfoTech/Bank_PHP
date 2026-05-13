<?php

namespace App\Models;

use App\Models\Movimiento;
use PDO;
use PDOException;
use Throwable;

/**
 * Representa una cuenta bancaria vinculada a un usuario.
 *
 * Contiene la logica de negocio basica para depositar, retirar y transferir,
 * con persistencia en MySQL cuando se proporciona PDO.
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

    private ?PDO $db = null;

    /**
     * Inicializa la cuenta con sus datos principales.
     */
    public function __construct(
        int $id,
        int $usuario_id,
        string $numero_cuenta,
        float $saldo = 0.0,
        bool $estado = true,
        ?PDO $db = null
    ) {
        $this->id = $id;
        $this->usuario_id = $usuario_id;
        $this->numero_cuenta = $numero_cuenta;
        $this->saldo = $saldo;
        $this->estado = $estado;
        $this->db = $db;
    }

    /**
     * Expone la conexion PDO asociada (misma referencia requerida en transferencias).
     */
    public function getPdo(): ?PDO
    {
        return $this->db;
    }

    /**
     * Verifica si la cuenta posee saldo suficiente para un monto dado.
     */
    public function validarSaldo(float $monto): bool
    {
        return $this->saldo >= $monto;
    }

    /**
     * Realiza un deposito y registra el movimiento.
     */
    public function depositar(float $monto): bool
    {
        if ($monto <= 0 || !$this->estado) {
            return false;
        }

        if ($this->db === null) {
            $this->saldo += $monto;
            $this->registrarMovimiento('deposito', $monto, 'Deposito en cuenta');

            return true;
        }

        try {
            $stmt = $this->db->prepare(
                'UPDATE cuentas SET saldo = saldo + :monto WHERE id = :id AND estado = 1'
            );
            $stmt->execute([
                'monto' => (string) $monto,
                'id' => $this->id,
            ]);

            if ($stmt->rowCount() !== 1) {
                return false;
            }

            $this->refrescarSaldoDesdeDb();
            $this->registrarMovimiento('deposito', $monto, 'Deposito en cuenta');

            return true;
        } catch (PDOException) {
            return false;
        }
    }

    /**
     * Realiza un retiro si existe saldo suficiente y la cuenta esta activa.
     */
    public function retirar(float $monto): bool
    {
        if ($monto <= 0 || !$this->estado || !$this->validarSaldo($monto)) {
            return false;
        }

        if ($this->db === null) {
            $this->saldo -= $monto;
            $this->registrarMovimiento('retiro', $monto, 'Retiro en cuenta');

            return true;
        }

        try {
            $stmt = $this->db->prepare(
                'UPDATE cuentas SET saldo = saldo - :monto WHERE id = :id AND estado = 1 AND saldo >= :monto2'
            );
            $stmt->execute([
                'monto' => (string) $monto,
                'id' => $this->id,
                'monto2' => (string) $monto,
            ]);

            if ($stmt->rowCount() !== 1) {
                return false;
            }

            $this->refrescarSaldoDesdeDb();
            $this->registrarMovimiento('retiro', $monto, 'Retiro en cuenta');

            return true;
        } catch (PDOException) {
            return false;
        }
    }

    /**
     * Transfiere fondos a otra cuenta.
     *
     * Con PDO, el debito y el credito se ejecutan en una transaccion atomica.
     */
    public function transferir(Cuenta $destino, float $monto): bool
    {
        if ($monto <= 0 || !$this->estado || !$destino->getEstado()) {
            return false;
        }

        if (!$this->validarSaldo($monto)) {
            return false;
        }

        if ($this->db === null && $destino->getPdo() === null) {
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

        if ($this->db === null || $destino->getPdo() === null || $this->db !== $destino->getPdo()) {
            return false;
        }

        $db = $this->db;
        $db->beginTransaction();

        try {
            $debit = $db->prepare(
                'UPDATE cuentas SET saldo = saldo - :monto WHERE id = :id AND estado = 1 AND saldo >= :monto2'
            );
            $debit->execute([
                'monto' => (string) $monto,
                'id' => $this->id,
                'monto2' => (string) $monto,
            ]);

            if ($debit->rowCount() !== 1) {
                $db->rollBack();

                return false;
            }

            $credit = $db->prepare(
                'UPDATE cuentas SET saldo = saldo + :monto WHERE id = :id AND estado = 1'
            );
            $credit->execute([
                'monto' => (string) $monto,
                'id' => $destino->getId(),
            ]);

            if ($credit->rowCount() !== 1) {
                $db->rollBack();

                return false;
            }

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

            $db->commit();

            $this->refrescarSaldoDesdeDb();
            $destino->refrescarSaldoDesdeDb();

            return true;
        } catch (Throwable) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            return false;
        }
    }

    /**
     * Registra un movimiento en memoria o en la tabla movimientos.
     */
    protected function registrarMovimiento(string $tipo, float $monto, string $descripcion): void
    {
        if ($this->db === null) {
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

            return;
        }

        $fecha = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare(
            'INSERT INTO movimientos (cuenta_id, tipo, monto, fecha, descripcion) VALUES (:cuenta_id, :tipo, :monto, :fecha, :descripcion)'
        );
        $stmt->execute([
            'cuenta_id' => $this->id,
            'tipo' => $tipo,
            'monto' => (string) $monto,
            'fecha' => $fecha,
            'descripcion' => $descripcion,
        ]);
    }

    private function refrescarSaldoDesdeDb(): void
    {
        if ($this->db === null) {
            return;
        }

        $stmt = $this->db->prepare('SELECT saldo FROM cuentas WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $this->id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row !== false) {
            $this->saldo = (float) $row['saldo'];
        }
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
        if ($this->db === null) {
            return $this->movimientos;
        }

        $stmt = $this->db->prepare(
            'SELECT id, cuenta_id, tipo, monto, fecha, descripcion
             FROM movimientos
             WHERE cuenta_id = :cuenta_id
             ORDER BY fecha DESC, id DESC'
        );
        $stmt->execute(['cuenta_id' => $this->id]);

        $lista = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lista[] = new Movimiento(
                (int) $row['id'],
                (int) $row['cuenta_id'],
                $row['tipo'],
                (float) $row['monto'],
                $row['fecha'],
                $row['descripcion'],
                $this->db
            );
        }

        return $lista;
    }
}
