<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;

abstract class Database
{
    private const DSN = 'mysql:host=localhost;dbname=banco_php;charset=utf8mb4';

    private static ?PDO $sharedPdo = null;

    protected ?PDO $pdo = null;

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
    
    protected function activarPersistencia(): void
    {
        $this->pdo = $this->getConnection();
    }
}