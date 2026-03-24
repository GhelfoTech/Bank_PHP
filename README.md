# Sistema Bancario Simulado - Base POO en PHP

Proyecto base orientado a objetos para un sistema bancario simulado, con estructura modular y separacion de roles, listo para migrar a MVC.

## Estructura del proyecto

```txt
BANCO - PHP/
├── index.php
├── README.md
├── assets/
│   └── css/
│       └── styles.css
└── models/
    ├── Usuario.php
    ├── Cuenta.php
    ├── Movimiento.php
    └── Roles/
        ├── Administrador.php
        └── Cliente.php
```

## Jerarquia de clases

- `Usuario` (abstracta)
  - Atributos base: `id`, `cedula`, `nombres`, `email`, `password`, `rol`, `estado`.
  - Define el metodo polimorfico `obtenerPermisos()`.
- `models/Roles/Cliente` extiende `Usuario`
  - Implementa `obtenerPermisos()` con capacidades operativas de cliente.
- `models/Roles/Administrador` extiende `Usuario`
  - Implementa `obtenerPermisos()` con capacidades administrativas.

## Flujo de datos

- **Usuario -> Cuenta**
  - `Cuenta` se asocia a un usuario mediante `usuario_id`.
  - Esto permite identificar al propietario de la cuenta dentro de la capa de dominio.

- **Cuenta -> Movimiento**
  - Al ejecutar `depositar`, `retirar` o `transferir`, la cuenta genera objetos `Movimiento`.
  - Los movimientos quedan guardados en un arreglo interno (`movimientos`) como historial temporal.

- **Transferencias**
  - `transferir(Cuenta $destino, $monto)` valida estado de cuentas y saldo disponible antes de operar.
  - Se registra salida en cuenta origen y entrada en cuenta destino.
  - La logica declara que en persistencia real debe usarse **Transaccion Obligatoria** para atomicidad.

## Principios POO aplicados

- **Abstraccion**: `Usuario` como clase abstracta.
- **Encapsulamiento**: atributos `private/protected` y uso de `getters/setters`.
- **Herencia**: `Cliente` y `Administrador` heredan de `Usuario`.
- **Polimorfismo**: `obtenerPermisos()` implementado de forma distinta en cada hijo.

## Vista de prueba

- `index.php` ahora renderiza una interfaz HTML:
  - resumen de administrador y cliente,
  - resultado de deposito,
  - resultado de transferencia fallida por saldo insuficiente,
  - saldos finales de ambas cuentas.
- Estilos en `assets/css/styles.css`.

## Como ejecutar localmente

### Opcion 1 (recomendada): servidor embebido de PHP

1. En la raiz del proyecto ejecutar:

```bash
php -S localhost:8000
```

2. Abrir en navegador:

- [http://localhost:8000/index.php](http://localhost:8000/index.php)

### Opcion 2: ejecucion por consola

```bash
php index.php
```

