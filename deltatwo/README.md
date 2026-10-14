# Logística Delta

Proyecto académico de logística. La web para clientes está en `public` y utiliza PHP y MySQL; el seguimiento público funciona con un código de envío o de paquete, sin crear una cuenta. Las cuentas son opcionales y permiten consultar “Mis envíos”. El recorrido del proyecto se limita a sucursales de Argentina.

## Puesta en marcha local

1. Iniciá Apache y MySQL desde XAMPP.
2. Importá `database/logistica_delta.sql` en una base nueva llamada `logistica_delta`.
3. Copiá el repositorio dentro de `htdocs` y abrí la web mediante `http://localhost/deltatwo/public/`.
4. Para probar el seguimiento, usá los códigos `DEL-0001` a `DEL-0004` o `ENV-0001` a `ENV-0004`.

La web necesita PHP y la base de datos local; abrir los HTML como archivos no ejecuta los endpoints.

## Panel de operaciones

El personal autorizado puede entrar desde `public/panel-admin.html` usando las cuentas con rol `ADMINISTRADOR`, `OPERADOR_DEPOSITO` o `ADMINISTRACION` de la base. El panel permite actualizar el estado y registrar sucursal, provincia y detalle; el cliente verá el nuevo movimiento al consultar el código público. El ejemplo incluido en el SQL tiene el usuario `operador` y la contraseña inicial `123456`; cambiala antes de usar datos reales.
