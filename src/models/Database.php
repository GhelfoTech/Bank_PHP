<?php

namespace App\Models;

use PDO;
use PDOException;

/**
 * Gestiona la conexión PDO a la base de datos MySQL del sistema bancario.
 */
class Database
{
    private const DSN = 'mysql:host=localhost;dbname=banco_php;charset=utf8mb4';

    private ?PDO $pdo = null;

    /**
     * Devuelve una instancia reutilizable de PDO (lazy).
     *
     * @throws PDOException si la conexión falla
     */
    public function getConnection(): PDO
    {
        if ($this->pdo === null) {
            $this->pdo = new PDO(self::DSN, 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return $this->pdo;
    }
}
