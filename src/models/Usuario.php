<?php

declare(strict_types=1);

namespace App\Models;

abstract class Usuario extends Database
{
    protected int $id;
    protected string $cedula;
    protected string $nombres;
    protected string $email;
    protected string $password;
    protected string $rol;
    protected bool $estado;

    public function __construct(
        int $id,
        string $cedula,
        string $nombres,
        string $email,
        string $password,
        string $rol,
        bool $estado = true
    ) {
        $this->id = $id;
        $this->cedula = $cedula;
        $this->nombres = $nombres;
        $this->email = $email;
        $this->password = $password;
        $this->rol = $rol;
        $this->estado = $estado;
    }

    abstract public function obtenerPermisos(): array;
    

    public function getId(): int { return $this->id; }

    public function setId(int $id): void { $this->id = $id; }


    public function getCedula(): string { return $this->cedula; }

    public function setCedula(string $cedula): void { $this->cedula = $cedula; }


    public function getNombres(): string { return $this->nombres; }

    public function setNombres(string $nombres): void { $this->nombres = $nombres; }


    public function getEmail(): string { return $this->email; }

    public function setEmail(string $email): void { $this->email = $email; }


    public function getPassword(): string { return $this->password; }

    public function setPassword(string $password): void { $this->password = $password; }


    public function getRol(): string { return $this->rol; }

    public function setRol(string $rol): void { $this->rol = $rol; }


    public function getEstado(): bool { return $this->estado; }

    public function setEstado(bool $estado): void { $this->estado = $estado; }
}