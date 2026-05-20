<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use Throwable;

class Cuenta extends Database
{
    private int $id;
    private int $usuario_id;
    private string $numero_cuenta;
    private float $saldo;
    private bool $estado;

    private array $movimientos = [];

    public function __construct(int $id, int $usuario_id, string $numero_cuenta, float $saldo = 0.0, bool $estado = true)
    {
        $this->id = $id;
        $this->usuario_id = $usuario_id;
        $this->numero_cuenta = $numero_cuenta;
        $this->saldo = $saldo;
        $this->estado = $estado;
    }

    public function activarPersistencia(): void
    {
        parent::activarPersistencia();
    }
    
    public function getPdo(): ?PDO
    {
        return $this->pdo;
    }
    
    public function validarSaldo(float $monto): bool
    {
        return $this->saldo >= $monto;
    }
    
    public function depositar(float $monto): bool
    {
        if ($monto <= 0 || !$this->estado) {
            return false;
        }
    
        if ($this->pdo === null) {
            $this->saldo += $monto;
            $this->registrarMovimiento('deposito', $monto, 'Deposito en cuenta');
            return true;
    
        }
        $db = $this->pdo;
        $db->beginTransaction();
    
        try {
            $stmt = $db->prepare('UPDATE cuentas SET saldo = saldo + :monto WHERE id = :id AND estado = 1');
            $stmt->execute(['monto' => (string) $monto, 'id' => $this->id]);
    
            if ($stmt->rowCount() !== 1) {
                $db->rollBack();
                return false;
            }
            
            $this->insertarMovimientoEnDb($db, 'deposito', $monto, 'Deposito en cuenta');
            $db->commit();
            $this->refrescarSaldoDesdeDb();
            return true;
    
        } catch (Throwable) {
            if ($db->inTransaction()) $db->rollBack();
            return false;
        }
    }
    
    public function retirar(float $monto): bool
    {
        if ($monto <= 0 || !$this->estado || !$this->validarSaldo($monto)) {
            return false;
        }
    
        if ($this->pdo === null) {
            $this->saldo -= $monto;
            $this->registrarMovimiento('retiro', $monto, 'Retiro en cuenta');
            return true;
    
        }
        $db = $this->pdo;
        $db->beginTransaction();
    
        try {
            $stmt = $db->prepare('UPDATE cuentas 
                                  SET saldo = saldo - :monto    
                                  WHERE id = :id 
                                  AND estado = 1 
                                  AND saldo >= :monto2');

            $stmt->execute(['monto' => (string) $monto, 'id' => $this->id, 'monto2' => (string) $monto]);
    
            if ($stmt->rowCount() !== 1) {
                $db->rollBack();
                return false;
    
            }
    
            $this->insertarMovimientoEnDb($db, 'retiro', $monto, 'Retiro en cuenta');
            $db->commit();
            $this->refrescarSaldoDesdeDb();
            return true;
    
        } catch (Throwable) {
            if ($db->inTransaction()) $db->rollBack();
            return false;
        }
    }
    
    public static function obtenerCuentaActivaConTitularPorNumero(string $numeroCuenta): ?array
    {
        $numero = trim($numeroCuenta);
    
        if ($numero === '') return null;
        $helper = new self(0, 0, '', 0.0, false);
        $db = $helper->getConnection();
        $stmt = $db->prepare('SELECT c.id, c.usuario_id, c.numero_cuenta, c.saldo, c.estado, u.cedula 
                              AS titular_cedula, u.nombres 
                              AS titular_nombres 
                              FROM cuentas c 
                              INNER JOIN usuarios u 
                              ON u.id = c.usuario_id 
                              AND u.estado = 1 
                              WHERE c.numero_cuenta = :numero 
                              AND c.estado = 1 
                              LIMIT 1');

        $stmt->execute(['numero' => $numero]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($row === false) return null;
        $cuenta = new self((int) $row['id'], 
                           (int) $row['usuario_id'], 
                           (string) $row['numero_cuenta'], 
                           (float) $row['saldo'], 
                           (bool) $row['estado']);

        $cuenta->activarPersistencia();
    
        return ['cuenta' => $cuenta, 
                'titular_cedula' => (string) $row['titular_cedula'], 
                'titular_nombres' => (string) $row['titular_nombres']];
    
    }
    
    private function construirDescripcionesTransferencia(Cuenta $destino, 
                                                         string $descripcionPersonalizada, 
                                                         string $nombreOrigen, 
                                                         string $nombreDestino): array
    {
        $sufijo = trim($descripcionPersonalizada) !== '' ? (' - ' . trim($descripcionPersonalizada)) : '';
        $nomOrigen = trim($nombreOrigen);
        $nomDestino = trim($nombreDestino);
    
        if ($nomOrigen !== '' && $nomDestino !== '') {
            return ['Transferencia enviada a ' . $nomDestino . $sufijo, 'Transferencia recibida de ' . $nomOrigen . $sufijo];
    
        }
        return ['Transferencia enviada a ' . $destino->getNumeroCuenta() . $sufijo, 
                'Transferencia recibida desde ' . $this->numero_cuenta . $sufijo];
    }
    
    public function transferir(Cuenta $destino, 
                               float $monto, 
                               string $descripcionPersonalizada = '', 
                               string $nombreOrigen = '', 
                               string $nombreDestino = ''): bool
    {
        if ($monto <= 0 || !$this->estado || !$destino->getEstado() || !$this->validarSaldo($monto)) return false;
        [$descEmisor, $descReceptor] = $this->construirDescripcionesTransferencia($destino, 
                                                                                  $descripcionPersonalizada, 
                                                                                  $nombreOrigen, 
                                                                                  $nombreDestino);
    
        if ($this->pdo === null && $destino->getPdo() === null) {
            $this->saldo -= $monto;
            $destino->saldo += $monto;
            $this->registrarMovimiento('transferencia', $monto, $descEmisor);
            $destino->registrarMovimiento('transferencia', $monto, $descReceptor);
    
            return true;
        }
        
        if ($this->pdo === null || $destino->getPdo() === null || $this->pdo !== $destino->getPdo()) return false;
        $db = $this->pdo;
        $db->beginTransaction();
        
        try {
            $debit = $db->prepare('UPDATE cuentas 
                                   SET saldo = saldo - :monto 
                                   WHERE id = :id 
                                   AND estado = 1 
                                   AND saldo >= :monto2');

            $debit->execute(['monto' => (string) $monto, 'id' => $this->id, 'monto2' => (string) $monto]);
            
            if ($debit->rowCount() !== 1) { $db->rollBack(); return false; }

            $credit = $db->prepare('UPDATE cuentas 
                                    SET saldo = saldo + :monto 
                                    WHERE id = :id 
                                    AND estado = 1');

            $credit->execute(['monto' => (string) $monto, 'id' => $destino->getId()]);
            
            if ($credit->rowCount() !== 1) { $db->rollBack(); return false; }

            $fecha = date('Y-m-d H:i:s');
            
            $this->insertarMovimientoEnDb($db, 'transferencia', $monto, $descEmisor, $fecha);
            $destino->insertarMovimientoEnDb($db, 'transferencia', $monto, $descReceptor, $fecha);
            $db->commit();
            
            $this->refrescarSaldoDesdeDb();
            $destino->refrescarSaldoDesdeDb();
        
            return true;
        
        } catch (Throwable) {
            if ($db->inTransaction()) $db->rollBack();
            return false;
        }
    }
    
    protected function registrarMovimiento(string $tipo, float $monto, string $descripcion): void
    {
        if ($this->pdo === null) {
            $this->movimientos[] = new Movimiento(count($this->movimientos) + 1, $this->id, $tipo, $monto, date('Y-m-d H:i:s'), $descripcion);
            return;
        }
    
        $this->insertarMovimientoEnDb($this->pdo, $tipo, $monto, $descripcion, date('Y-m-d H:i:s'));
    }
    
    private function insertarMovimientoEnDb(PDO $db, 
                                            string $tipo, 
                                            float $monto, 
                                            string $descripcion, 
                                            ?string $fecha = null): void
    {
        $stmt = $db->prepare('INSERT INTO movimientos (cuenta_id, usuario_id, tipo, monto, fecha, descripcion) 
                              VALUES (:cuenta_id, :usuario_id, :tipo, :monto, :fecha, :descripcion)');

        $stmt->execute(['cuenta_id' => $this->id, 
                        'usuario_id' => $this->usuario_id, 
                        'tipo' => $tipo, 
                        'monto' => (string) $monto, 
                        'fecha' => $fecha ?? date('Y-m-d H:i:s'), 
                        'descripcion' => $descripcion]);
    }
    
    private function refrescarSaldoDesdeDb(): void
    {
        if ($this->pdo === null) return;
        
        $stmt = $this->pdo->prepare('SELECT saldo 
                                     FROM cuentas 
                                     WHERE id = :id 
                                     LIMIT 1');

        $stmt->execute(['id' => $this->id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($row !== false) $this->saldo = (float) $row['saldo'];
    }
    
    public function getId(): int { return $this->id; }
    
    public function setId(int $id): void { $this->id = $id; }

    
    public function getUsuarioId(): int { return $this->usuario_id; }
    
    public function setUsuarioId(int $usuario_id): void { $this->usuario_id = $usuario_id; }

    
    public function getNumeroCuenta(): string { return $this->numero_cuenta; }
    
    public function setNumeroCuenta(string $numero_cuenta): void { $this->numero_cuenta = $numero_cuenta; }

    
    public function getSaldo(): float { return $this->saldo; }
    
    public function setSaldo(float $saldo): void { $this->saldo = $saldo; }

    
    public function getEstado(): bool { return $this->estado; }
    
    public function setEstado(bool $estado): void { $this->estado = $estado; }

    
    public function getMovimientos(): array
    {
        if ($this->pdo === null) return $this->movimientos;

        $stmt = $this->pdo->prepare('SELECT id, cuenta_id, tipo, monto, fecha, descripcion 
                                     FROM movimientos 
                                     WHERE cuenta_id = :cuenta_id 
                                     ORDER BY fecha 
                                     DESC, id 
                                     DESC');

        $stmt->execute(['cuenta_id' => $this->id]);
        $lista = [];
    
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lista[] = new Movimiento((int) $row['id'], 
                                      (int) $row['cuenta_id'], $row['tipo'], 
                                      (float) $row['monto'], $row['fecha'], $row['descripcion']);
        }
    
        return $lista;
    }
    
    public static function obtenerMovimientosPorUsuarioId(int $usuarioId): array
    {
        $db = (new self(0, 0, '', 0.0, false))->getConnection();
        
        $stmt = $db->prepare('SELECT m.id, m.cuenta_id, m.tipo, m.monto, m.fecha, m.descripcion 
                              FROM movimientos m 
                              INNER JOIN cuentas c 
                              ON c.id = m.cuenta_id 
                              WHERE c.usuario_id = :uid 
                              AND c.estado = 1 
                              ORDER BY m.fecha 
                              DESC, m.id 
                              DESC');

        $stmt->execute(['uid' => $usuarioId]);
        $lista = [];
    
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lista[] = new Movimiento((int) $row['id'], 
                                      (int) $row['cuenta_id'], 
                                      (string) $row['tipo'], 
                                      (float) $row['monto'], 
                                      (string) $row['fecha'], 
                                      (string) $row['descripcion']);
        }
    
        return $lista;
    }
}