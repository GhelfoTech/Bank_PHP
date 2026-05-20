<?php

namespace App\Models;

use PDO;
use PDOException;

/**
 * Gestiona la conexión PDO a la base de datos MySQL del sistema bancario.
 */
abstract class Database
{
    private const DSN = 'mysql:host=localhost;dbname=banco_php;charset=utf8mb4';

    private static ?PDO $sharedPdo = null;

    protected ?PDO $pdo = null;

    /**
     * Devuelve una instancia reutilizable de PDO (lazy, compartida entre entidades).
     *
     * @throws PDOException si la conexión falla
     */
    protected function getConnection(): PDO
    {
        if (self::$sharedPdo === null) {
            self::$sharedPdo = new PDO(self::DSN, 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        $this->pdo = self::$sharedPdo;

        return self::$sharedPdo;
    }

    /**
     * Vincula la conexión compartida a la entidad (equivalente al PDO inyectado antes).
     */
    protected function activarPersistencia(): void
    {
        $this->pdo = $this->getConnection();
    }
}
