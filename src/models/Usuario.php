<?php

namespace App\Models;

/**
 * Clase abstracta base para representar usuarios del sistema bancario.
 *
 * Centraliza los atributos comunes y define el contrato polimorfico
 * de permisos para cada tipo de usuario.
 */
abstract class Usuario
{
    protected int $id;
    protected string $cedula;
    protected string $nombres;
    protected string $email;
    protected string $password;
    protected string $rol;
    protected bool $estado;
    protected ?\PDO $db = null;

    /**
     * Inicializa un usuario con sus datos principales.
     */
    public function __construct(
        int $id,
        string $cedula,
        string $nombres,
        string $email,
        string $password,
        string $rol,
        bool $estado = true,
        ?\PDO $db = null
    ) {
        $this->id = $id;
        $this->cedula = $cedula;
        $this->nombres = $nombres;
        $this->email = $email;
        $this->password = $password;
        $this->rol = $rol;
        $this->estado = $estado;
        $this->db = $db;
    }

    /**
     * Devuelve los permisos del usuario segun su tipo.
     */
    abstract public function obtenerPermisos(): array;

    /**
     * Obtiene el identificador del usuario.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Define el identificador del usuario.
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Obtiene la cedula del usuario.
     */
    public function getCedula(): string
    {
        return $this->cedula;
    }

    /**
     * Define la cedula del usuario.
     */
    public function setCedula(string $cedula): void
    {
        $this->cedula = $cedula;
    }

    /**
     * Obtiene los nombres del usuario.
     */
    public function getNombres(): string
    {
        return $this->nombres;
    }

    /**
     * Define los nombres del usuario.
     */
    public function setNombres(string $nombres): void
    {
        $this->nombres = $nombres;
    }

    /**
     * Obtiene el correo electronico del usuario.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Define el correo electronico del usuario.
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * Obtiene la contrasena del usuario.
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Define la contrasena del usuario.
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Obtiene el rol del usuario.
     */
    public function getRol(): string
    {
        return $this->rol;
    }

    /**
     * Define el rol del usuario.
     */
    public function setRol(string $rol): void
    {
        $this->rol = $rol;
    }

    /**
     * Indica si el usuario se encuentra activo.
     */
    public function getEstado(): bool
    {
        return $this->estado;
    }

    /**
     * Define el estado activo/inactivo del usuario.
     */
    public function setEstado(bool $estado): void
    {
        $this->estado = $estado;
    }
}
