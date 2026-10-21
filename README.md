# Logística Delta

Logística Delta es una aplicación web para registrar y consultar envíos y paquetes. El sistema reúne la información de clientes, envíos y movimientos para que los clientes puedan conocer el estado de sus paquetes y el personal pueda mantener actualizado su recorrido.

Este proyecto académico se desarrolló para la materia Laboratorio de Aplicaciones Web Dinámicas. Está pensado para la operación de Logística Delta en CABA y el Gran Buenos Aires, con conexión al resto del país mediante empresas de transporte asociadas.

## Funcionalidades

- Consulta pública de envíos mediante el código del envío o del paquete, sin necesidad de iniciar sesión.
- Historial de seguimiento con los cambios de estado y las sucursales registradas.
- Registro e inicio de sesión para clientes, con solicitud de envíos y consulta de sus movimientos.
- Panel para personal autorizado, desde el que se actualizan estados y ubicaciones.
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
3. En phpMyAdmin, creá una base llamada `logistica_delta` e importá allí `database/logistica_delta.sql` para cargar las tablas y los datos de ejemplo.
4. Abrí `http://localhost/deltatwo/public/` en el navegador.

La conexión a la base de datos está centralizada en `app/config/Conexion.php`.

## Pruebas de seguimiento

Después de importar la base, podés consultar estos códigos en la página de seguimiento:

| Código de envío | Código de paquete |
| --- | --- |
| `ENV-0001` | `DEL-0001` |
| `ENV-0002` | `DEL-0002` |
| `ENV-0003` | `DEL-0003` |
| `ENV-0004` | `DEL-0004` |

## Acceso al panel de operaciones

El panel está disponible en `http://localhost/deltatwo/public/panel-admin.php`. Para la base de ejemplo, se incluyen estas cuentas locales:

| Usuario | Contraseña | Rol |
| --- | --- | --- |
| `admin` | `DeltaAdmin123` | Administrador |
| `operador` | `123456` | Operador de depósito |

Estas credenciales son únicamente para la instalación local de práctica. Si ya tenías una copia de la base importada antes de agregar el acceso del personal, ejecutá `database/habilitar_acceso_personal.sql` desde phpMyAdmin con `logistica_delta` seleccionada.

## Organización del código

- `public/`: páginas del sitio, formularios, estilos y JavaScript.
- `app/config/`: conexión a MySQL.
- `app/dao/`: consultas y operaciones sobre la base de datos.
- `app/helpers/`: funciones de sesión, permisos y salida segura de datos.
- `app/models/`: clases que representan entidades del sistema.
- `database/`: estructura y datos de ejemplo.

Para conocer los recorridos principales, las responsabilidades de cada capa y los archivos útiles para presentar el proyecto, consultá [FUNCIONAMIENTO.md](FUNCIONAMIENTO.md).
