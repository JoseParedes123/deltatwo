# Estructura inicial

El proyecto toma como referencia la separacion del ejemplo SpotCloud, adaptada a los nombres en espanol y a las tablas de `logistica_delta`.

```text
logisticaDeltas/
  app/
    config/Conexion.php
    dao/EnvioDAO.php
    models/Envio.php
  database/logistica_delta.sql
  public/
    css/style.css
    js/app.js
    partials/Footer.html
    consultar_seguimiento.php
    index.html
    seguimiento.html
    ...otras paginas visuales...
```

## Responsabilidades

- `config`: conexion PDO y configuracion del servidor.
- `models`: clases que representan conceptos de la base, por ejemplo `Envio`, `Usuario`, `Cliente` y `Paquete`.
- `dao`: consultas preparadas a MySQL, separadas por entidad, como `EnvioDAO`.
- `public`: paginas, recursos y endpoints accesibles desde el navegador.

El seguimiento usa `EnvioDAO` para consultar por codigo y relacionar contactos y paquete; el DAO devuelve un objeto `Envio`. El endpoint publica los datos a la pagina. Las clases de las otras tablas se pueden agregar cuando se implementen esos flujos, sin crear archivos vacios para toda la base.

## Base de datos y ejecucion

1. Crear una base vacia llamada `logistica_delta` en phpMyAdmin e importar `database/logistica_delta.sql`. La base local actual esta incompleta: falta la tabla `contactos`. Respaldala antes de reemplazarla si contiene datos que quieras conservar.
2. Por defecto, PDO usa `localhost`, usuario `root` y contrasena vacia. Se pueden definir `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASSWORD` en el entorno.
3. Servir la carpeta `public` desde Apache/XAMPP. Por ejemplo, al colocar `logisticaDeltas` dentro de `htdocs`, abrir `http://localhost/logisticaDeltas/`.
4. En seguimiento, probar los codigos de ejemplo `ENV-0001` a `ENV-0004` que vienen en el SQL.

No se implementa historial de movimientos porque el esquema actual no registra eventos de seguimiento por envio; solo guarda el estado vigente en `envios.estado`.