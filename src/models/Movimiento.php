<?php

declare(strict_types=1);

namespace App\Models;

class Movimiento extends Database
{
    private int $id;
    private int $cuenta_id;
    private string $tipo;
    private float $monto;
    private string $fecha;
    private string $descripcion;

    public function __construct(int $id, int $cuenta_id, string $tipo, float $monto, string $fecha, string $descripcion)
    {
        $this->id = $id;
        $this->cuenta_id = $cuenta_id;
        $this->tipo = $tipo;
        $this->monto = $monto;
        $this->fecha = $fecha;
        $this->descripcion = $descripcion;
    }

    public function getId(): int { return $this->id; }
    
    public function setId(int $id): void { $this->id = $id; }


    public function getCuentaId(): int { return $this->cuenta_id; }

    public function setCuentaId(int $cuenta_id): void { $this->cuenta_id = $cuenta_id; }


    public function getTipo(): string { return $this->tipo; }

    public function setTipo(string $tipo): void { $this->tipo = $tipo; }


    public function getMonto(): float { return $this->monto; }

    public function setMonto(float $monto): void { $this->monto = $monto; }


    public function getFecha(): string { return $this->fecha; }

    public function setFecha(string $fecha): void { $this->fecha = $fecha; }


    public function getDescripcion(): string { return $this->descripcion; }

    public function setDescripcion(string $descripcion): void { $this->descripcion = $descripcion; }
}