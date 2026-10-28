<?php
require_once __DIR__ . '/../app/helpers/funciones.php';
require_once __DIR__ . '/../app/config/Conexion.php';
require_once __DIR__ . '/../app/dao/UsuarioDAO.php';
require_once __DIR__ . '/../app/dao/EnvioDAO.php';
require_once __DIR__ . '/../app/dao/MovimientoSeguimientoDAO.php';
iniciarSesion();
$idiomaPanel = idiomaActual();

$rolesPersonal = ['ADMINISTRADOR', 'OPERADOR_DEPOSITO', 'ADMINISTRACION'];
$error = '';
$mensaje = mensajeFlasheado() ?? '';
$datos = ['codigo' => '', 'estado' => 'REGISTRADO', 'sucursal' => '', 'provincia' => '', 'detalle' => ''];
// Sucursales confirmadas del corredor norte de la provincia de Buenos Aires.
$sucursales = [
    'olivos_maipu_2683' => ['localidad' => 'Olivos', 'direccion' => 'Avenida Maipú 2683', 'provincia' => 'Buenos Aires'],
    'olivos_maipu_3028' => ['localidad' => 'Olivos', 'direccion' => 'Avenida Maipú 3028', 'provincia' => 'Buenos Aires'],
    'olivos_uruguay_1841' => ['localidad' => 'Olivos', 'direccion' => 'Calle Uruguay 1841', 'provincia' => 'Buenos Aires'],
    'olivos_uruguay_2404' => ['localidad' => 'Olivos', 'direccion' => 'Calle Uruguay 2404', 'provincia' => 'Buenos Aires'],
    'olivos_maipu_2881' => ['localidad' => 'Olivos', 'direccion' => 'Avenida Maipú 2881', 'provincia' => 'Buenos Aires'],
    'munro_velez_4319' => ['localidad' => 'Munro', 'direccion' => 'Avenida Vélez Sarsfield 4319', 'provincia' => 'Buenos Aires'],
    'munro_velez_4819' => ['localidad' => 'Munro', 'direccion' => 'Avenida Vélez Sarsfield 4819', 'provincia' => 'Buenos Aires'],
    'munro_velez_4945' => ['localidad' => 'Munro', 'direccion' => 'Avenida Vélez Sarsfield 4945', 'provincia' => 'Buenos Aires'],
    'munro_mitre_3870' => ['localidad' => 'Munro', 'direccion' => 'Avenida Mitre 3870', 'provincia' => 'Buenos Aires'],
    'carapachay_deheza_5798' => ['localidad' => 'Carapachay', 'direccion' => 'Calle Deheza 5798', 'provincia' => 'Buenos Aires'],
    'villa_adelina_parana_6001' => ['localidad' => 'Villa Adelina', 'direccion' => 'Avenida Paraná 6001', 'provincia' => 'Buenos Aires'],
    'villa_ballester_buenos_aires_4479' => ['localidad' => 'Villa Ballester', 'direccion' => 'Calle Buenos Aires 4479', 'provincia' => 'Buenos Aires'],
    'villa_ballester_buenos_aires_4755' => ['localidad' => 'Villa Ballester', 'direccion' => 'Calle Buenos Aires 4755', 'provincia' => 'Buenos Aires'],
    'villa_ballester_lacroze_1850' => ['localidad' => 'Villa Ballester', 'direccion' => 'Calle Lacroze 1850', 'provincia' => 'Buenos Aires'],
    'villa_ballester_marengo_4479' => ['localidad' => 'Villa Ballester', 'direccion' => 'Calle Marengo 4479', 'provincia' => 'Buenos Aires'],
    'san_martin_campos_1951' => ['localidad' => 'San Martín', 'direccion' => 'Calle Campos 1951', 'provincia' => 'Buenos Aires'],
    'san_martin_pueyrredon_4694' => ['localidad' => 'San Martín', 'direccion' => 'Calle Pueyrredón 4694', 'provincia' => 'Buenos Aires'],
    'san_martin_mitre_3166' => ['localidad' => 'San Martín', 'direccion' => 'Calle Mitre 3166', 'provincia' => 'Buenos Aires'],
    'san_martin_belgrano_4514' => ['localidad' => 'San Martín', 'direccion' => 'Calle Belgrano 4514', 'provincia' => 'Buenos Aires'],
    'san_martin_pellegrini_1980' => ['localidad' => 'San Martín', 'direccion' => 'Calle Pellegrini 1980', 'provincia' => 'Buenos Aires'],
    'tigre_cazon_1140' => ['localidad' => 'Tigre', 'direccion' => 'Avenida Cazón 1140', 'provincia' => 'Buenos Aires'],
];

// Este archivo procesa el acceso del personal y las actualizaciones del recorrido.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'login') {
        try {
            $usuario = (new UsuarioDAO())->validarLogin(trim($_POST['usuario'] ?? ''), $_POST['password'] ?? '');
            if ($usuario && in_array($usuario['rol'], $rolesPersonal, true)) {
                session_regenerate_id(true);
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['rol'] = $usuario['rol'];
                header('Location: panel-admin.php');
                exit;
            }
            $error = t('Credenciales incorrectas o cuenta sin permisos de personal.');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $error = t('No pudimos iniciar sesión en este momento. Intentá nuevamente más tarde.');
        }
    } elseif ($accion === 'logout') {
        $_SESSION = [];
        $_SESSION['idioma'] = $idiomaPanel;
        header('Location: panel-admin.php?lang=' . urlencode($idiomaPanel));
        exit;
    } elseif ($accion === 'actualizar' && in_array($_SESSION['rol'] ?? '', $rolesPersonal, true)) {
        foreach ($datos as $campo => $valor) $datos[$campo] = trim($_POST[$campo] ?? $valor);
        $estados = ['REGISTRADO', 'RECIBIDO_DEPOSITO', 'PREPARADO', 'EN_TRANSITO', 'EN_REPARTO', 'ENTREGADO', 'NO_ENTREGADO', 'DEVUELTO', 'EXTRAVIADO', 'CANCELADO'];
        if (!in_array($datos['estado'], $estados, true) || !$datos['codigo']
            || !isset($sucursales[$datos['sucursal']]) || !$datos['detalle']
            || mb_strlen($datos['detalle']) > 255) {
            $error = t('Revisá el código, estado, sucursal y detalle.');
        } else {
            $db = null;
            try {
                $db = (new Conexion())->conectar();
                $envioDAO = new EnvioDAO($db);
                $idEnvio = $envioDAO->obtenerIdPorCodigo(strtoupper($datos['codigo']));
                if (!$idEnvio) {
                    $error = t('No encontramos un envío activo con ese código.');
                } else {
                    $db->beginTransaction();
                    $envioDAO->cambiarEstado($idEnvio, $datos['estado']);
                    $sucursalElegida = $sucursales[$datos['sucursal']];
                    $nombreSucursal = $sucursalElegida['localidad'] . ' · ' . $sucursalElegida['direccion'];
                    (new MovimientoSeguimientoDAO($db))->crear($idEnvio, $nombreSucursal, $sucursalElegida['provincia'], $datos['detalle']);
                    $db->commit();
                    $_SESSION['mensaje'] = t('Estado y recorrido actualizados. El cliente ya puede ver el movimiento.');
                    header('Location: panel-admin.php');
                    exit;
                }
            } catch (Throwable $exception) {
                if ($db && $db->inTransaction()) $db->rollBack();
                error_log($exception->getMessage());
                $error = t('No pudimos guardar el movimiento en este momento. Intentá nuevamente más tarde.');
            }
        }
    } else {
        $error = 'Iniciá sesión con una cuenta de personal para realizar esa acción.';
    }
}

$usuarioActual = usuarioActual();
$envios = [];
// Solo el personal autorizado puede consultar el listado de operaciones.
if ($usuarioActual && in_array($usuarioActual['rol'], $rolesPersonal, true)) {
    try {
        $envios = (new EnvioDAO())->obtenerParaPanel();
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        $error = t('No pudimos cargar los envíos en este momento. Intentá recargar la página.');
    }
}
$datos['codigo'] = $_GET['codigo'] ?? $datos['codigo'];
?>
<!doctype html>
<html lang="<?= limpiar(idiomaActual()) ?>">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#073486"><title><?= limpiar(t('Operaciones')) ?> | Logística Delta</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include __DIR__ . '/partials/nav.php'; ?>
  <main class="contenedor pagina-operaciones">
    <h1><?= limpiar(t('Panel de operaciones')) ?></h1>
    <p class="bajada"><?= limpiar(t('Actualizá el estado y la sucursal para que el cliente pueda consultar el recorrido de su paquete.')) ?></p>

    <!-- La vista cambia entre el acceso del personal y las herramientas operativas. -->
    <?php if (!$usuarioActual || !in_array($usuarioActual['rol'], $rolesPersonal, true)): ?>
      <section class="panel-operaciones">
        <h2><?= limpiar(t('Acceso de personal')) ?></h2>
        <?php if ($error): ?><p class="respuesta-operaciones" role="alert"><?= limpiar($error) ?></p><?php endif; ?>
        <form method="post" class="formulario-operaciones">
          <input type="hidden" name="accion" value="login">
          <label><?= limpiar(t('Usuario o email')) ?><input name="usuario" autocomplete="username" required></label>
          <label><?= limpiar(t('Contraseña')) ?><input name="password" type="password" autocomplete="current-password" required></label>
          <button class="boton-principal" type="submit"><?= limpiar(t('Ingresar')) ?></button>
        </form>
      </section>
    <?php else: ?>
      <div class="barra-operaciones">
        <p><?= limpiar(t('Hola, ')) ?><?= limpiar($usuarioActual['nombre']) ?> · <?= limpiar(t(str_replace('_', ' ', strtolower($usuarioActual['rol']))) ) ?></p>
        <form method="post"><input type="hidden" name="accion" value="logout"><button class="salida-operaciones" type="submit"><?= limpiar(t('Cerrar sesión')) ?></button></form>
      </div>
      <?php if ($error || $mensaje): ?><p class="respuesta-operaciones" role="status"><?= limpiar($error ?: $mensaje) ?></p><?php endif; ?>
      <div class="columnas-operaciones">
        <section class="panel-operaciones">
          <h2><?= limpiar(t('Actualizar recorrido')) ?></h2>
          <form method="post" class="formulario-operaciones">
            <input type="hidden" name="accion" value="actualizar">
            <label><?= limpiar(t('Código del envío')) ?><input name="codigo" placeholder="<?= limpiar(t('Ej.: ENV-0001')) ?>" required value="<?= limpiar($datos['codigo']) ?>"></label>
            <label><?= limpiar(t('Estado')) ?><select name="estado" required>
              <?php foreach (['REGISTRADO' => 'Registrado', 'RECIBIDO_DEPOSITO' => 'Recibido en depósito', 'PREPARADO' => 'Preparado', 'EN_TRANSITO' => 'En tránsito', 'EN_REPARTO' => 'En reparto', 'ENTREGADO' => 'Entregado', 'NO_ENTREGADO' => 'No entregado', 'DEVUELTO' => 'Devuelto', 'EXTRAVIADO' => 'Extraviado', 'CANCELADO' => 'Cancelado'] as $value => $label): ?>
                <option value="<?= limpiar($value) ?>" <?= $datos['estado'] === $value ? 'selected' : '' ?>><?= limpiar(t($label)) ?></option>
              <?php endforeach; ?>
            </select></label>
            <label class="campo-amplio"><?= limpiar(t('Sucursal')) ?><select name="sucursal" required>
              <option value=""><?= limpiar(t('Elegí una sucursal de Buenos Aires')) ?></option>
              <?php $localidades = array_unique(array_column($sucursales, 'localidad')); foreach ($localidades as $localidad): ?>
                <optgroup label="<?= limpiar($localidad) ?>">
                  <?php foreach ($sucursales as $clave => $sucursal): if ($sucursal['localidad'] !== $localidad) continue; ?>
                    <option value="<?= limpiar($clave) ?>" <?= $datos['sucursal'] === $clave ? 'selected' : '' ?>><?= limpiar(str_replace(['Avenida', 'Calle'], [t('Avenida'), t('Calle')], $sucursal['direccion'])) ?></option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endforeach; ?>
            </select></label>
            <label class="campo-amplio"><?= limpiar(t('Detalle para el cliente')) ?><textarea name="detalle" maxlength="255" required><?= limpiar($datos['detalle']) ?></textarea></label>
            <button class="boton-principal campo-amplio" type="submit"><?= limpiar(t('Guardar movimiento')) ?></button>
          </form>
        </section>
        <section class="panel-operaciones">
          <h2><?= limpiar(t('Envíos recientes')) ?></h2>
          <?php if (!$envios): ?><p><?= limpiar(t('Todavía no hay envíos activos.')) ?></p><?php endif; ?>
          <div class="lista-envios-operaciones">
            <?php foreach ($envios as $envio): ?>
              <a class="elemento-envio-operaciones" href="panel-admin.php?codigo=<?= urlencode($envio['codigo_envio']) ?>">
                <strong><?= limpiar($envio['codigo_envio']) ?> · <?= limpiar(estadoLegible($envio['estado'])) ?></strong>
                <span><?= limpiar(t('Ubicación:')) ?> <?= limpiar($envio['ubicacion'] ?: t('Sin movimientos cargados')) ?></span>
                <span><?= limpiar(t('Destino:')) ?> <?= limpiar($envio['destino']) ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </section>
      </div>
    <?php endif; ?>
  </main>
  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
