# Logística Delta

Logística Delta es una aplicación web para registrar y consultar envíos y paquetes. El sistema reúne la información de clientes, envíos y movimientos para que los clientes puedan conocer el estado de sus paquetes y el personal pueda mantener actualizado su recorrido.

Este proyecto académico se desarrolló para la materia Diseño de Aplicaciones Web Dinámicas. La cobertura de envíos de la aplicación está limitada a las localidades bonaerenses incluidas en la base de datos.

## Funcionalidades

- Consulta pública de envíos mediante el código del envío o del paquete, sin necesidad de iniciar sesión.
- Historial de seguimiento con los cambios de estado y las sucursales registradas.
- Registro e inicio de sesión para clientes, con solicitud de envíos y consulta de sus movimientos.
- Panel para personal autorizado, desde el que se actualizan estados y ubicaciones.
- Bandeja de Atención al Cliente para responder y hacer seguimiento de las consultas del formulario.
- Formulario de contacto para enviar consultas a la empresa.
- Interfaz adaptable a computadoras, tablets y celulares.

## Tecnologías

- PHP 8.2
- MySQL
- HTML5
- CSS3
- JavaScript

## Requisitos

- XAMPP con Apache, PHP y MySQL.
- Un navegador actualizado, como Chrome, Firefox, Edge o Safari.

## Instalación local

1. Cloná o copiá el repositorio dentro de la carpeta `htdocs` de XAMPP.
2. Iniciá Apache y MySQL desde el panel de XAMPP.
3. En phpMyAdmin, creá una base vacía llamada `logistica_delta` e importá allí `database/logistica_delta.sql`. Para migrar la base del hosting, hacé primero un respaldo y ejecutá una sola vez `database/migrar_host_s11.sql` sobre la base existente. Nunca importes el SQL de instalación sobre una base con datos.
4. Abrí `http://localhost/deltatwo/public/` en el navegador.

La conexión PHP se configura en `app/config/Conexion.php` mediante variables de entorno (`DELTA_DB_HOST`, `DELTA_DB_PORT`, `DELTA_DB_NAME`, `DELTA_DB_USER`, `DELTA_DB_PASSWORD`) o el archivo local ignorado `app/config/Conexion.local.php`. Java toma `DELTA_DB_URL`, `DELTA_DB_USER` y `DELTA_DB_PASSWORD` del entorno o de propiedades Java. Para que ambas aplicaciones compartan los datos, configurá ambas con el mismo servidor y la misma base.

## Acceso al panel de operaciones

El panel está disponible en `http://localhost/deltatwo/public/panel-admin.php`. Para la base de ejemplo, se incluyen estas cuentas locales:

| Usuario | Contraseña | Rol |
| --- | --- | --- |
| `admin` | `DeltaAdmin123` | Administrador |
| `operador` | `123456` | Operador de depósito |
| `atencion` | `DeltaAtencion123` | Atención al cliente |

Estas credenciales son únicamente para una instalación local nueva de práctica; cambiá las claves antes de publicar el sitio.

El rol `ATENCION_CLIENTE` tiene una bandeja propia en `public/atencion-cliente.php`; solo ese rol puede entrar. En una base de ejemplo recién importada, iniciá sesión con la cuenta `atencion`. Para agregarla a una base ya instalada, ejecutá `database/crear_usuario_atencion.sql` desde phpMyAdmin. La persona prepara la respuesta en la bandeja, la envía desde su correo y después la marca como respondida.

## Organización del código

- `public/`: páginas del sitio, formularios, estilos y JavaScript.
- `app/config/`: conexión a MySQL.
- `app/dao/`: consultas y operaciones sobre la base de datos.
- `app/helpers/`: funciones de sesión, permisos y salida segura de datos.
- `app/models/`: clases que representan entidades del sistema.
- `database/`: estructura y datos de ejemplo.

Para conocer los recorridos principales, las responsabilidades de cada capa y los archivos útiles para presentar el proyecto, consultá [FUNCIONAMIENTO.md](FUNCIONAMIENTO.md).
