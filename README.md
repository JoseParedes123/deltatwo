# Logística Delta

Proyecto académico de logística. La web para clientes está en `public` y utiliza PHP y MySQL; el seguimiento público funciona con un código de envío o de paquete, sin crear una cuenta. Las cuentas son opcionales y permiten consultar “Mis envíos”. El recorrido del proyecto se limita a sucursales de Argentina.

## Puesta en marcha local

1. Iniciá Apache y MySQL desde XAMPP.
2. Importá `database/logistica_delta.sql` en una base nueva llamada `logistica_delta`.
3. Copiá el repositorio dentro de `htdocs` y abrí la web mediante `http://localhost/deltatwo/public/`.
4. Para probar el seguimiento, usá los códigos `DEL-0001` a `DEL-0004` o `ENV-0001` a `ENV-0004`.

Las pantallas se sirven con PHP y necesitan la base de datos local.

La explicación de las pantallas, las capas y los recorridos principales del código está en [`FUNCIONAMIENTO.md`](FUNCIONAMIENTO.md).

## Panel de operaciones

El personal autorizado puede entrar desde `public/panel-admin.php` usando las cuentas con rol `ADMINISTRADOR`, `OPERADOR_DEPOSITO` o `ADMINISTRACION` de la base. El registro público crea clientes; no permite asignar permisos de personal. Para probar el panel, el SQL incluye `admin` / `DeltaAdmin123` y `operador` / `123456`. Si la base ya estaba importada, ejecutá `database/habilitar_acceso_personal.sql` desde phpMyAdmin con `logistica_delta` seleccionada. Estas credenciales son solo para la práctica local.
