<?php

namespace App\Models;

use App\Models\Movimiento;
use PDO;
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

        $db = $this->db;
        $db->beginTransaction();

        try {
            $stmt = $db->prepare(
                'UPDATE cuentas SET saldo = saldo + :monto WHERE id = :id AND estado = 1'
            );
            $stmt->execute([
                'monto' => (string) $monto,
                'id' => $this->id,
            ]);

            if ($stmt->rowCount() !== 1) {
                $db->rollBack();

                return false;
            }

            $this->insertarMovimientoEnDb($db, 'deposito', $monto, 'Deposito en cuenta');
            $db->commit();
            $this->refrescarSaldoDesdeDb();

            return true;
        } catch (Throwable) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

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

        $db = $this->db;
        $db->beginTransaction();

        try {
            $stmt = $db->prepare(
                'UPDATE cuentas SET saldo = saldo - :monto WHERE id = :id AND estado = 1 AND saldo >= :monto2'
            );
            $stmt->execute([
                'monto' => (string) $monto,
                'id' => $this->id,
                'monto2' => (string) $monto,
            ]);

            if ($stmt->rowCount() !== 1) {
                $db->rollBack();

                return false;
            }

            $this->insertarMovimientoEnDb($db, 'retiro', $monto, 'Retiro en cuenta');
            $db->commit();
            $this->refrescarSaldoDesdeDb();

            return true;
        } catch (Throwable) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            return false;
        }
    }

    /**
     * Busca una cuenta activa por numero y devuelve la entidad con datos del titular.
     *
     * @return array{cuenta: Cuenta, titular_cedula: string, titular_nombres: string}|null
     */
    public static function obtenerCuentaActivaConTitularPorNumero(PDO $db, string $numeroCuenta): ?array
    {
        $numero = trim($numeroCuenta);
        if ($numero === '') {
            return null;
        }

        $stmt = $db->prepare(
            'SELECT c.id, c.usuario_id, c.numero_cuenta, c.saldo, c.estado,
                    u.cedula AS titular_cedula, u.nombres AS titular_nombres
             FROM cuentas c
             INNER JOIN usuarios u ON u.id = c.usuario_id AND u.estado = 1
             WHERE c.numero_cuenta = :numero AND c.estado = 1
             LIMIT 1'
        );
        $stmt->execute(['numero' => $numero]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        $cuenta = new Cuenta(
            (int) $row['id'],
            (int) $row['usuario_id'],
            (string) $row['numero_cuenta'],
            (float) $row['saldo'],
            (bool) $row['estado'],
            $db
        );

        return [
            'cuenta' => $cuenta,
            'titular_cedula' => (string) $row['titular_cedula'],
            'titular_nombres' => (string) $row['titular_nombres'],
        ];
    }

    /**
     * Construye textos de movimiento para transferencia (emisor y receptor).
     */
    private function construirDescripcionesTransferencia(
        Cuenta $destino,
        string $descripcionPersonalizada,
        string $nombreOrigen,
        string $nombreDestino
    ): array {
        $extra = trim($descripcionPersonalizada);
        $sufijo = $extra !== '' ? (' - ' . $extra) : '';

        $nomOrigen = trim($nombreOrigen);
        $nomDestino = trim($nombreDestino);

        if ($nomOrigen !== '' && $nomDestino !== '') {
            return [
                'Transferencia enviada a ' . $nomDestino . $sufijo,
                'Transferencia recibida de ' . $nomOrigen . $sufijo,
            ];
        }

        return [
            'Transferencia enviada a ' . $destino->getNumeroCuenta() . $sufijo,
            'Transferencia recibida desde ' . $this->numero_cuenta . $sufijo,
        ];
    }

    /**
     * Transfiere fondos a otra cuenta.
     *
     * Con PDO, el debito, el credito y los movimientos se ejecutan en una transaccion atomica.
     *
     * @param string $descripcionPersonalizada Texto opcional anexado tras el nombre del contraparte.
     * @param string $nombreOrigen             Nombre del titular emisor (para leyenda en destino).
     * @param string $nombreDestino            Nombre del titular receptor (para leyenda en origen).
     */
    public function transferir(
        Cuenta $destino,
        float $monto,
        string $descripcionPersonalizada = '',
        string $nombreOrigen = '',
        string $nombreDestino = ''
    ): bool {
        if ($monto <= 0 || !$this->estado || !$destino->getEstado()) {
            return false;
        }

        if (!$this->validarSaldo($monto)) {
            return false;
        }

        [$descEmisor, $descReceptor] = $this->construirDescripcionesTransferencia(
            $destino,
            $descripcionPersonalizada,
            $nombreOrigen,
            $nombreDestino
        );

        if ($this->db === null && $destino->getPdo() === null) {
            $this->saldo -= $monto;
            $destino->saldo += $monto;

            $this->registrarMovimiento('transferencia', $monto, $descEmisor);
            $destino->registrarMovimiento('transferencia', $monto, $descReceptor);

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

            $fecha = date('Y-m-d H:i:s');
            $this->insertarMovimientoEnDb($db, 'transferencia', $monto, $descEmisor, $fecha);
            $destino->insertarMovimientoEnDb($db, 'transferencia', $monto, $descReceptor, $fecha);

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
        $this->insertarMovimientoEnDb($this->db, $tipo, $monto, $descripcion, $fecha);
    }

    /**
     * Inserta una fila en movimientos (debe ejecutarse dentro de una transaccion activa cuando aplique).
     */
    private function insertarMovimientoEnDb(
        PDO $db,
        string $tipo,
        float $monto,
        string $descripcion,
        ?string $fecha = null
    ): void {
        $fecha = $fecha ?? date('Y-m-d H:i:s');
        $stmt = $db->prepare(
            'INSERT INTO movimientos (cuenta_id, usuario_id, tipo, monto, fecha, descripcion)
             VALUES (:cuenta_id, :usuario_id, :tipo, :monto, :fecha, :descripcion)'
        );
        $stmt->execute([
            'cuenta_id' => $this->id,
            'usuario_id' => $this->usuario_id,
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

    /**
     * Obtiene todos los movimientos de las cuentas activas de un usuario.
     *
     * @return Movimiento[]
     */
    public static function obtenerMovimientosPorUsuarioId(PDO $db, int $usuarioId): array
    {
        $stmt = $db->prepare(
            'SELECT m.id, m.cuenta_id, m.tipo, m.monto, m.fecha, m.descripcion
             FROM movimientos m
             INNER JOIN cuentas c ON c.id = m.cuenta_id
             WHERE c.usuario_id = :uid AND c.estado = 1
             ORDER BY m.fecha DESC, m.id DESC'
        );
        $stmt->execute(['uid' => $usuarioId]);

        $lista = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lista[] = new Movimiento(
                (int) $row['id'],
                (int) $row['cuenta_id'],
                (string) $row['tipo'],
                (float) $row['monto'],
                (string) $row['fecha'],
                (string) $row['descripcion'],
                $db
            );
        }

        return $lista;
    }
}
