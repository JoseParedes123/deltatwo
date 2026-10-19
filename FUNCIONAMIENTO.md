# Cómo está organizada Logística Delta

Logística Delta es un proyecto académico para administrar y seguir paquetes dentro de Argentina. Las páginas se renderizan con PHP: cada apartado carga los datos que necesita, procesa sus formularios y llama a los DAOs correspondientes. JavaScript queda para interacciones de pantalla, como abrir el menú en móviles.

## Qué puede hacer cada persona

- **Visitante:** consultar servicios, enviar una consulta y seguir un paquete por código, sin registrarse.
- **Cliente:** entrar a su cuenta, solicitar envíos y consultar sus movimientos.
- **Personal autorizado:** actualizar estado, sucursal, provincia y detalle desde el panel.

## Estructura del proyecto

```text
public/
  index.php, servicios.php, contacto.php, redes.php   Páginas públicas
  seguimiento.php, login.php, registro.php             Formularios de clientes
  envios.php, panel-admin.php, logout.php              Cuenta y operaciones
  partials/                                             Navegación y pie compartidos
  css/                                                  Estilos de la web
  js/app.js                                             Menú adaptable a móviles
app/
  config/                                               Conexión PDO con MySQL
  dao/                                                  Consultas y cambios de datos
  helpers/                                              Sesión, permisos y escape HTML
  models/                                               Clases que representan datos
database/                                               Tablas y registros de ejemplo
```

La página PHP recibe el formulario y valida la sesión y los campos. Después llama al DAO que corresponde; el DAO ejecuta el SQL preparado. En operaciones que escriben en varias tablas, la página comparte una conexión PDO entre DAOs y maneja una transacción para guardar todo junto.

```text
formulario → página PHP del apartado → DAO → MySQL
                  ↑                    ↓
             vuelve a mostrar       resultados
```

No hay una carpeta `api`: las acciones pertenecen al archivo de la pantalla que las presenta. Por ejemplo, `seguimiento.php` tanto muestra el formulario como procesa el código ingresado y dibuja el resultado.

## Recorridos principales

### Registro e inicio de sesión

`registro.php` recibe los datos por POST, `UsuarioDAO` registra la cuenta y `ClienteDAO` crea el perfil. Ambas escrituras usan una transacción. `login.php` consulta `UsuarioDAO`, valida el rol de cliente e inicia la sesión.

### Seguimiento público

`seguimiento.php` recibe el código en la misma página. `EnvioDAO` busca un envío por su código o por el código del paquete; `MovimientoSeguimientoDAO` carga el historial de sucursales. PHP genera la tarjeta y la línea de tiempo dentro de la página, sin pedir datos a JavaScript.

### Solicitar un envío

`envios.php` muestra la cuenta y procesa el formulario de solicitud. Coordina `ClienteDAO`, `ZonaDAO`, `TarifaDAO`, `PaqueteDAO`, `ContactoDAO`, `EnvioDAO` y `MovimientoSeguimientoDAO`. La transacción agrupa el paquete, los contactos, el envío y su primer movimiento.

### Actualizar movimientos

`panel-admin.php` muestra el formulario de acceso o el panel según la sesión. `UsuarioDAO` valida al personal; `EnvioDAO` carga los envíos y actualiza el estado; `MovimientoSeguimientoDAO` guarda la nueva ubicación que se verá en el seguimiento público.

### Contacto

`contacto.php` valida el mensaje, `EnvioDAO` asocia un código si se indicó y `ConsultaWebDAO` guarda la consulta.

## Archivos para explicar en clase

- `app/config/Conexion.php`: punto único de configuración para conectar PHP con MySQL.
- `app/helpers/funciones.php`: sesión, protección de páginas y escape de texto para HTML.
- `app/models/Envio.php`: propiedades y getters que representan un envío.
- `app/dao/EnvioDAO.php`: búsquedas, listado, creación y cambios de estado de envíos.
- `app/dao/MovimientoSeguimientoDAO.php`: guarda y lista las sucursales recorridas.
- `app/dao/ConsultaWebDAO.php`: registra los mensajes del formulario de contacto.
- `public/seguimiento.php`: ejemplo de una página que recibe un formulario, llama a DAOs y muestra los datos.
- `public/envios.php`: ejemplo de un flujo que coordina varios DAOs dentro de una transacción.
- `public/panel-admin.php`: ejemplo del acceso del personal y la actualización de movimientos.
- `public/partials/nav.php` y `footer.php`: componentes compartidos incluidos desde las páginas.
- `public/css/style.css`: colores y composición de los bocetos, con tamaños y espacios adaptados a cada pantalla.

## Cómo levantar el sitio

1. Iniciá Apache y MySQL desde XAMPP.
2. Importá `database/logistica_delta.sql` en una base llamada `logistica_delta`.
3. Abrí `http://localhost/deltatwo/public/`.
4. El panel interno está en `http://localhost/deltatwo/public/panel-admin.php`.

El SQL de ejemplo trae las cuentas `admin` / `DeltaAdmin123` y `operador` / `123456`. Si ya habías importado la base, ejecutá `database/habilitar_acceso_personal.sql` desde phpMyAdmin. Son credenciales para la práctica local. Los códigos de prueba están en `README.md`.

## Criterio visual

Se conservaron los colores, las formas redondeadas y los bloques de los bocetos. Se ajustaron alturas, títulos, márgenes y controles; además, tarjetas, imágenes y formularios se reorganizan en pantallas angostas para mantener una composición clara.
