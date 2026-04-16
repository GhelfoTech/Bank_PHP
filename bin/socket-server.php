<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Socket\Notificador;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Notificador()
        )
    ),
    8080
);

echo "Servidor de WebSockets iniciado en el puerto 8080..." . PHP_EOL;

$server->run();
