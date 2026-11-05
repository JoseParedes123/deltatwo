# Cómo está organizada Logística Delta

Logística Delta es un proyecto académico para administrar y seguir paquetes en las localidades de la provincia de Buenos Aires incluidas en la base. Las páginas se renderizan con PHP: cada apartado carga los datos que necesita, procesa sus formularios y llama a los DAOs correspondientes. JavaScript queda para interacciones de pantalla, como abrir el menú en móviles.

## Qué puede hacer cada persona

- **Visitante:** consultar servicios, enviar una consulta y seguir un paquete por código, sin registrarse.
- **Cliente:** entrar a su cuenta, solicitar envíos y consultar sus movimientos.
- **Personal de operaciones:** actualizar estado, sucursal y detalle desde el panel.
- **Atención al Cliente:** revisar consultas, preparar respuestas y registrar cuándo se enviaron.

## Estructura del proyecto

```text
public/
  index.php, servicios.php, contacto.php, redes.php   Páginas públicas
  seguimiento.php, login.php, registro.php             Formularios de clientes
  envios.php, panel-admin.php, atencion-cliente.php     Cuenta y paneles internos
  logout.php                                            Cierre de sesión
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

Todos los DAO pueden recibir esa conexión PDO al crearse. Si no se les pasa una, abren la conexión configurada en `Conexion`; así las páginas simples siguen igual y los flujos con transacciones comparten la misma conexión.

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

### Atender consultas
`contacto.php` guarda cada consulta en `consultas_web`. El rol `ATENCION_CLIENTE` entra a `atencion-cliente.php`, prepara una respuesta y abre un correo dirigido al cliente con el texto cargado. Después de enviar el correo, el trabajador marca la consulta como respondida. La respuesta, el estado y el usuario que la preparó quedan registrados en la base.

### Contacto

`contacto.php` valida el mensaje, `EnvioDAO` asocia un código si se indicó y `ConsultaWebDAO` guarda la consulta.

### Idioma

`funciones.php` guarda la preferencia de idioma en la sesión y ofrece `t()` para traducir los textos de la interfaz. El diccionario inglés vive en `app/lang/en.php`; si una frase todavía no tiene traducción, se muestra en español.

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
- `public/atencion-cliente.php` y `app/dao/ConsultaWebDAO.php`: bandeja, preparación de respuestas y estado de atención.
- `public/partials/nav.php` y `footer.php`: componentes compartidos incluidos desde las páginas.
- `public/css/style.css`: colores y composición de los bocetos, con tamaños y espacios adaptados a cada pantalla.

## Cómo levantar el sitio

1. Iniciá Apache y MySQL desde XAMPP.
2. Para instalar desde cero, creá una base vacía llamada `logistica_delta` e importá `database/logistica_delta.sql`. Para conservar la base del hosting, primero hacé un respaldo y ejecutá una sola vez `database/migrar_host_s11.sql` sobre la base existente. No importes el SQL de instalación sobre la base con datos.
3. Abrí `http://localhost/deltatwo/public/`.
4. El panel interno está en `http://localhost/deltatwo/public/panel-admin.php`.

El SQL de ejemplo trae las cuentas `admin` / `DeltaAdmin123` y `operador` / `123456`. Estas cuentas son solo para la base de práctica local; cambiá las claves antes de publicar. Los códigos de prueba están en `README.md`.

La tabla de Atención al Cliente y el rol `ATENCION_CLIENTE` ya están incluidos en el esquema compartido. El envío de correo se realiza desde el programa de correo del trabajador.

## Criterio visual

Se conservaron los colores, las formas redondeadas y los bloques de los bocetos. Se ajustaron alturas, títulos, márgenes y controles; además, tarjetas, imágenes y formularios se reorganizan en pantallas angostas para mantener una composición clara.
