<?php

namespace App\Socket;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Notificador implements MessageComponentInterface
{
    public function onOpen(ConnectionInterface $conn): void
    {
        echo "Nueva conexion detectada! ID: {$conn->resourceId}" . PHP_EOL;
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        echo "Mensaje recibido de {$from->resourceId}: {$msg}" . PHP_EOL;
    }

    public function onClose(ConnectionInterface $conn): void
    {
        echo "Conexion cerrada. ID: {$conn->resourceId}" . PHP_EOL;
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        echo "Error en conexion {$conn->resourceId}: {$e->getMessage()}" . PHP_EOL;
        $conn->close();
    }
}
