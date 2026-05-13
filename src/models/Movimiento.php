<?php

namespace App\Models;

/**
 * Representa un movimiento financiero asociado a una cuenta.
 *
 * Se utiliza como entidad de trazabilidad para depositos,
 * retiros y transferencias.
 */
class Movimiento
{
    private int $id;
    private int $cuenta_id;
    private string $tipo;
    private float $monto;
    private string $fecha;
    private string $descripcion;
    private ?\PDO $db = null;

    /**
     * Inicializa un movimiento con su informacion principal.
     */
    public function __construct(
        int $id,
        int $cuenta_id,
        string $tipo,
        float $monto,
        string $fecha,
        string $descripcion,
        ?\PDO $db = null
    ) {
        $this->id = $id;
        $this->cuenta_id = $cuenta_id;
        $this->tipo = $tipo;
        $this->monto = $monto;
        $this->fecha = $fecha;
        $this->descripcion = $descripcion;
        $this->db = $db;
    }

    /**
     * Obtiene el identificador del movimiento.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Define el identificador del movimiento.
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Obtiene el id de la cuenta asociada.
     */
    public function getCuentaId(): int
    {
        return $this->cuenta_id;
    }

    /**
     * Define el id de la cuenta asociada.
     */
    public function setCuentaId(int $cuenta_id): void
    {
        $this->cuenta_id = $cuenta_id;
    }

    /**
     * Obtiene el tipo de movimiento.
     */
    public function getTipo(): string
    {
        return $this->tipo;
    }

    /**
     * Define el tipo de movimiento.
     */
    public function setTipo(string $tipo): void
    {
        $this->tipo = $tipo;
    }

    /**
     * Obtiene el monto del movimiento.
     */
    public function getMonto(): float
    {
        return $this->monto;
    }

    /**
     * Define el monto del movimiento.
     */
    public function setMonto(float $monto): void
    {
        $this->monto = $monto;
    }

    /**
     * Obtiene la fecha del movimiento.
     */
    public function getFecha(): string
    {
        return $this->fecha;
    }

    /**
     * Define la fecha del movimiento.
     */
    public function setFecha(string $fecha): void
    {
        $this->fecha = $fecha;
    }

    /**
     * Obtiene la descripcion del movimiento.
     */
    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    /**
     * Define la descripcion del movimiento.
     */
    public function setDescripcion(string $descripcion): void
    {
        $this->descripcion = $descripcion;
    }
}
