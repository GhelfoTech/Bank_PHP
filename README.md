# Sistema Bancario Simulado - Base POO en PHP

Proyecto base orientado a objetos para un sistema bancario simulado, con estructura modular.

## Estructura del proyecto

```txt
BANCO - PHP/
├── index.php
├── README.md
└── models/
    ├── Usuario.php
    ├── Cliente.php
    ├── Administrador.php
    ├── Cuenta.php
    └── Movimiento.php
```

## Jerarquia de clases

- `Usuario` (abstracta)
  - Atributos base: `id`, `cedula`, `nombres`, `email`, `password`, `rol`, `estado`.
  - Define el metodo polimorfico `obtenerPermisos()`.
- `Cliente` extiende `Usuario`
  - Implementa `obtenerPermisos()` con capacidades operativas de cliente.
- `Administrador` extiende `Usuario`
  - Implementa `obtenerPermisos()` con capacidades administrativas.

### Otras entidades

- `Cuenta`
  - Atributos: `id`, `usuario_id`, `numero_cuenta`, `saldo`, `estado`.
  - Metodos de negocio: `validarSaldo($monto)`, `depositar($monto)`, `retirar($monto)`, `transferir(Cuenta $destino, $monto)`.
- `Movimiento`
  - Atributos: `id`, `cuenta_id`, `tipo`, `monto`, `fecha`, `descripcion`.
  - Sirve como registro de trazabilidad en memoria para operaciones de cuenta.

## Flujo de datos

- **Usuario -> Cuenta**
  - `Cuenta` se asocia a un usuario mediante `usuario_id`.
  - Esto permite identificar al propietario de la cuenta dentro de la capa de dominio.

- **Cuenta -> Movimiento**
  - Al ejecutar `depositar`, `retirar` o `transferir`, la cuenta genera objetos `Movimiento`.
  - Los movimientos quedan guardados en un arreglo interno (`movimientos`) como historial temporal.

- **Transferencias**
  - El metodo `transferir` valida estado de cuentas y saldo disponible antes de operar.
  - Se registra un movimiento de salida en la cuenta origen y otro de entrada en la cuenta destino.
  - La logica deja documentado que, al migrar a base de datos, debe implementarse como **Transaccion Obligatoria** para asegurar atomicidad.

## Principios POO aplicados

- **Abstraccion**: `Usuario` como clase abstracta.
- **Encapsulamiento**: atributos `private/protected` y uso de `getters/setters`.
- **Herencia**: `Cliente` y `Administrador` heredan de `Usuario`.
- **Polimorfismo**: `obtenerPermisos()` implementado de forma distinta en cada hijo.

