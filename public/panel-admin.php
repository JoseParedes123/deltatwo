<?php
require_once __DIR__ . '/../app/helpers/funciones.php';
require_once __DIR__ . '/../app/config/Conexion.php';
require_once __DIR__ . '/../app/dao/EnvioDAO.php';
require_once __DIR__ . '/../app/dao/MovimientoSeguimientoDAO.php';
require_once __DIR__ . '/../app/dao/SucursalDAO.php';
iniciarSesion();
$rolesPersonal = ['ADMINISTRADOR', 'SUPERADMIN', 'OPERADOR_DEPOSITO', 'ADMINISTRACION'];
$usuarioActual = usuarioActual();
if (!$usuarioActual) {
    header('Location: login.php');
    exit;
}
if ($usuarioActual['rol'] === 'CLIENTE') {
    header('Location: envios.php');
    exit;
}
if (!in_array($usuarioActual['rol'], $rolesPersonal, true)) {
    header('Location: login.php');
    exit;
}
$error = '';
$mensaje = mensajeFlasheado() ?? '';
$datos = ['codigo' => '', 'estado' => 'REGISTRADO', 'sucursal' => '', 'detalle' => ''];

// procesa las actualizaciones de estado y recorrido del personal autenticado.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'actualizar' && in_array($_SESSION['rol'] ?? '', $rolesPersonal, true)) {
        foreach ($datos as $campo => $valor) $datos[$campo] = trim($_POST[$campo] ?? $valor);
        $estados = ['REGISTRADO', 'RECIBIDO_DEPOSITO', 'PREPARADO', 'EN_TRANSITO', 'EN_REPARTO', 'ENTREGADO', 'NO_ENTREGADO', 'DEVUELTO', 'EXTRAVIADO', 'CANCELADO'];
        $idSucursal = filter_var($datos['sucursal'], FILTER_VALIDATE_INT);
        if (!in_array($datos['estado'], $estados, true) || !$datos['codigo']
            || !$idSucursal || !$datos['detalle']
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
                    $sucursalElegida = (new SucursalDAO($db))->obtenerActivaPorId($idSucursal);
                    if (!$sucursalElegida) {
                        throw new DomainException('La sucursal seleccionada ya no está disponible.');
                    }
                    $db->beginTransaction();
                    if (!$envioDAO->cambiarEstado($idEnvio, $datos['estado'], $usuarioActual['id_usuario'])) {
                        throw new DomainException('No se pudo guardar el cambio de estado.');
                    }
                    (new MovimientoSeguimientoDAO($db))->crear($idEnvio, $idSucursal, $datos['detalle']);
                    $db->commit();
                    $_SESSION['mensaje'] = t('Estado y recorrido actualizados. El cliente ya puede ver el movimiento.');
                    header('Location: panel-admin.php');
                    exit;
                }
            } catch (Throwable $exception) {
                if ($db && $db->inTransaction()) $db->rollBack();
                error_log($exception->getMessage());
                    $error = $exception instanceof DomainException
                        ? t($exception->getMessage())
                        : t('No pudimos guardar el movimiento en este momento. Intentá nuevamente más tarde.');
            }
        }
    } else {
        $error = t('La acción solicitada no es válida.');
    }
}


$envios = [];
$sucursales = [];
// solo el personal autorizado puede consultar el listado de operaciones.
if ($usuarioActual && in_array($usuarioActual['rol'], $rolesPersonal, true)) {
    try {
        $envios = (new EnvioDAO())->obtenerParaPanel();
        $sucursales = (new SucursalDAO())->obtenerActivas();
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
  <link rel="icon" type="image/png" href="media/favicon.png">
  <meta name="theme-color" content="#073486"><title><?= limpiar(t('Operaciones')) ?> | Logística Delta</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include __DIR__ . '/partials/nav.php'; ?>
  <main class="contenedor pagina-operaciones">
    <h1><?= limpiar(t('Panel de operaciones')) ?></h1>
    <p class="bajada"><?= limpiar(t('Actualizá el estado y la sucursal para que el cliente pueda consultar el recorrido de su paquete.')) ?></p>


      <div class="barra-operaciones">
        <p><?= limpiar(t('Hola, ')) ?><?= limpiar($usuarioActual['nombre']) ?> · <?= limpiar(t(str_replace('_', ' ', strtolower($usuarioActual['rol']))) ) ?></p>
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
                  <?php foreach ($sucursales as $sucursal): if ($sucursal['localidad'] !== $localidad) continue; ?>
                    <option value="<?= (int) $sucursal['id_sucursal'] ?>" <?= (string) $datos['sucursal'] === (string) $sucursal['id_sucursal'] ? 'selected' : '' ?>><?= limpiar($sucursal['direccion']) ?></option>
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
  </main>
  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
