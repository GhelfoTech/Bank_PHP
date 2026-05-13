# Sistema Bancario Simulado en PHP

Proyecto demostrativo orientado a objetos con estructura tipo MVC, autoloading PSR-4 mediante Composer y base de infraestructura WebSocket con Ratchet.

## Estructura actual del proyecto

```text
BANCO - PHP/
├── bin/
│   └── socket-server.php
├── src/
│   ├── controllers/
│   │   └── BancoController.php
│   ├── models/
│   │   ├── Cuenta.php
│   │   ├── Movimiento.php
│   │   ├── Usuario.php
│   │   └── Roles/
│   │       ├── Administrador.php
│   │       └── Cliente.php
│   ├── views/
│   │   └── banco_view.php
│   └── Socket/
│       └── Notificador.php
├── vendor/
├── composer.json
├── composer.lock
└── index.php
```

## Autoloading y Namespaces

El proyecto utiliza Composer con mapeo PSR-4:

```json
"autoload": {
  "psr-4": {
    "App\\": "src/"
  }
}
```

Namespaces principales:

- `App\Controllers` para controladores en `src/controllers/`
- `App\Models` para modelos base en `src/models/`
- `App\Models\Roles` para roles en `src/models/Roles/`
- `App\Socket` para infraestructura WebSocket en `src/Socket/`

El punto de entrada `index.php` carga solo `vendor/autoload.php` y luego instancia el controlador con `use App\Controllers\BancoController;`.

## Capa de dominio (POO)

### Jerarquia de clases

- `Usuario` (abstracta):
  - Centraliza atributos comunes (`id`, `cedula`, `nombres`, `email`, `password`, `rol`, `estado`).
  - Define el metodo polimorfico `obtenerPermisos()`.
- `Roles\Cliente` extiende `Usuario`:
  - Implementa permisos operativos del cliente.
- `Roles\Administrador` extiende `Usuario`:
  - Implementa permisos administrativos.

### Flujo funcional

- `Cuenta` se asocia a `Usuario` por `usuario_id`.
- `Cuenta` registra objetos `Movimiento` en memoria al depositar, retirar o transferir.
- `transferir(Cuenta $destino, float $monto)` valida estados/saldo y registra movimientos de salida/entrada.

## Simulacion actual (controlador + vista)

`BancoController`:

- Crea instancias de administrador y cliente.
- Crea cuenta origen y destino.
- Simula un deposito y una transferencia.
- Expone a la vista variables de estado y mensaje:
  - `$depositoExitoso`, `$transferenciaExitosa`
  - `$depositoEstado`, `$transferenciaEstado`
  - `$depositoMensaje`, `$transferenciaMensaje`

`banco_view.php` renderiza los datos y muestra los mensajes de la simulacion en la seccion de trazabilidad de movimientos.

## Infraestructura WebSocket (nuevo)

### 1) Componente de mensajes

Archivo: `src/Socket/Notificador.php`

- Namespace: `App\Socket`
- Implementa `Ratchet\MessageComponentInterface`
- Logs implementados:
  - `onOpen`: nueva conexion y `resourceId`
  - `onMessage`: mensaje recibido y `resourceId`
  - `onClose`: cierre de conexion
  - `onError`: error de conexion y cierre del socket

### 2) Script del servidor

Archivo: `bin/socket-server.php`

- Carga `vendor/autoload.php`
- Levanta `IoServer` + `HttpServer` + `WsServer`
- Usa `new App\Socket\Notificador()`
- Escucha en el puerto `8080`
- Imprime al iniciar:
  - `Servidor de WebSockets iniciado en el puerto 8080...`

## Instalacion

```bash
composer install
```

Si realizas cambios en clases/namespaces, regenera autoload:

```bash
composer dump-autoload
```

### Servidor WebSocket

En otra terminal:

```bash
php bin/socket-server.php
```

TRUNCATE TABLE movimientos;