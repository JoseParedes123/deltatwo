<?php
require_once __DIR__ . '/../app/helpers/funciones.php';
require_once __DIR__ . '/../app/config/Conexion.php';
require_once __DIR__ . '/../app/dao/UsuarioDAO.php';
require_once __DIR__ . '/../app/dao/EnvioDAO.php';
require_once __DIR__ . '/../app/dao/MovimientoSeguimientoDAO.php';
iniciarSesion();

$rolesPersonal = ['ADMINISTRADOR', 'OPERADOR_DEPOSITO', 'ADMINISTRACION'];
$error = '';
$mensaje = mensajeFlasheado() ?? '';
$datos = ['codigo' => '', 'estado' => 'REGISTRADO', 'sucursal' => '', 'provincia' => '', 'detalle' => ''];

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
            $error = 'Credenciales incorrectas o cuenta sin permisos de personal.';
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $error = 'No pudimos iniciar sesión. Revisá que MySQL esté activo.';
        }
    } elseif ($accion === 'logout') {
        $_SESSION = [];
        session_destroy();
        header('Location: panel-admin.php');
        exit;
    } elseif ($accion === 'actualizar' && in_array($_SESSION['rol'] ?? '', $rolesPersonal, true)) {
        foreach ($datos as $campo => $valor) $datos[$campo] = trim($_POST[$campo] ?? $valor);
        $estados = ['REGISTRADO', 'RECIBIDO_DEPOSITO', 'PREPARADO', 'EN_TRANSITO', 'EN_REPARTO', 'ENTREGADO', 'NO_ENTREGADO', 'DEVUELTO', 'EXTRAVIADO', 'CANCELADO'];
        $provincias = ['Ciudad Autónoma de Buenos Aires', 'Buenos Aires', 'Catamarca', 'Chaco', 'Chubut', 'Córdoba', 'Corrientes', 'Entre Ríos', 'Formosa', 'Jujuy', 'La Pampa', 'La Rioja', 'Mendoza', 'Misiones', 'Neuquén', 'Río Negro', 'Salta', 'San Juan', 'San Luis', 'Santa Cruz', 'Santa Fe', 'Santiago del Estero', 'Tierra del Fuego', 'Tucumán'];

        if (!in_array($datos['estado'], $estados, true) || !$datos['codigo'] || !$datos['sucursal']
            || !in_array($datos['provincia'], $provincias, true) || !$datos['detalle']
            || mb_strlen($datos['sucursal']) > 100 || mb_strlen($datos['detalle']) > 255) {
            $error = 'Revisá el código, estado, sucursal, provincia y detalle.';
        } else {
            $db = null;
            try {
                $db = (new Conexion())->conectar();
                $envioDAO = new EnvioDAO($db);
                $idEnvio = $envioDAO->obtenerIdPorCodigo(strtoupper($datos['codigo']));
                if (!$idEnvio) {
                    $error = 'No encontramos un envío activo con ese código.';
                } else {
                    $db->beginTransaction();
                    $envioDAO->cambiarEstado($idEnvio, $datos['estado']);
                    (new MovimientoSeguimientoDAO($db))->crear($idEnvio, $datos['sucursal'], $datos['provincia'], $datos['detalle']);
                    $db->commit();
                    $_SESSION['mensaje'] = 'Estado y recorrido actualizados. El cliente ya puede ver el movimiento.';
                    header('Location: panel-admin.php');
                    exit;
                }
            } catch (Throwable $exception) {
                if ($db && $db->inTransaction()) $db->rollBack();
                error_log($exception->getMessage());
                $error = 'No pudimos guardar el movimiento. Revisá que MySQL esté activo.';
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
        $error = 'No pudimos cargar los envíos. Revisá la conexión con MySQL.';
    }
}
$datos['codigo'] = $_GET['codigo'] ?? $datos['codigo'];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#073486"><title>Operaciones | Logística Delta</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include __DIR__ . '/partials/nav.php'; ?>
  <main class="contenedor pagina-operaciones">
    <h1>Panel de operaciones</h1>
    <p class="bajada">Actualizá el estado y la sucursal para que el cliente pueda consultar el recorrido de su paquete.</p>

    <?php if (!$usuarioActual || !in_array($usuarioActual['rol'], $rolesPersonal, true)): ?>
      <section class="panel-operaciones">
        <h2>Acceso de personal</h2>
        <?php if ($error): ?><p class="respuesta-operaciones" role="alert"><?= limpiar($error) ?></p><?php endif; ?>
        <form method="post" class="formulario-operaciones">
          <input type="hidden" name="accion" value="login">
          <label>Usuario o email<input name="usuario" autocomplete="username" required></label>
          <label>Contraseña<input name="password" type="password" autocomplete="current-password" required></label>
          <button class="boton-principal" type="submit">Ingresar</button>
        </form>
      </section>
    <?php else: ?>
      <div class="barra-operaciones">
        <p>Hola, <?= limpiar($usuarioActual['nombre']) ?> · <?= limpiar(str_replace('_', ' ', strtolower($usuarioActual['rol']))) ?></p>
        <form method="post"><input type="hidden" name="accion" value="logout"><button class="salida-operaciones" type="submit">Cerrar sesión</button></form>
      </div>
      <?php if ($error || $mensaje): ?><p class="respuesta-operaciones" role="status"><?= limpiar($error ?: $mensaje) ?></p><?php endif; ?>
      <div class="columnas-operaciones">
        <section class="panel-operaciones">
          <h2>Actualizar recorrido</h2>
          <form method="post" class="formulario-operaciones">
            <input type="hidden" name="accion" value="actualizar">
            <label>Código del envío<input name="codigo" placeholder="Ej.: ENV-0001" required value="<?= limpiar($datos['codigo']) ?>"></label>
            <label>Estado<select name="estado" required>
              <?php foreach (['REGISTRADO' => 'Registrado', 'RECIBIDO_DEPOSITO' => 'Recibido en depósito', 'PREPARADO' => 'Preparado', 'EN_TRANSITO' => 'En tránsito', 'EN_REPARTO' => 'En reparto', 'ENTREGADO' => 'Entregado', 'NO_ENTREGADO' => 'No entregado', 'DEVUELTO' => 'Devuelto', 'EXTRAVIADO' => 'Extraviado', 'CANCELADO' => 'Cancelado'] as $value => $label): ?>
                <option value="<?= limpiar($value) ?>" <?= $datos['estado'] === $value ? 'selected' : '' ?>><?= limpiar($label) ?></option>
              <?php endforeach; ?>
            </select></label>
            <label>Sucursal o centro<input name="sucursal" placeholder="Centro de distribución Córdoba" maxlength="100" required value="<?= limpiar($datos['sucursal']) ?>"></label>
            <label>Provincia<select name="provincia" required><option value="">Elegí una provincia</option>
              <?php foreach (['Ciudad Autónoma de Buenos Aires', 'Buenos Aires', 'Catamarca', 'Chaco', 'Chubut', 'Córdoba', 'Corrientes', 'Entre Ríos', 'Formosa', 'Jujuy', 'La Pampa', 'La Rioja', 'Mendoza', 'Misiones', 'Neuquén', 'Río Negro', 'Salta', 'San Juan', 'San Luis', 'Santa Cruz', 'Santa Fe', 'Santiago del Estero', 'Tierra del Fuego', 'Tucumán'] as $provincia): ?>
                <option value="<?= limpiar($provincia) ?>" <?= $datos['provincia'] === $provincia ? 'selected' : '' ?>><?= limpiar($provincia) ?></option>
              <?php endforeach; ?>
            </select></label>
            <label class="campo-amplio">Detalle para el cliente<textarea name="detalle" maxlength="255" required><?= limpiar($datos['detalle']) ?></textarea></label>
            <button class="boton-principal campo-amplio" type="submit">Guardar movimiento</button>
          </form>
        </section>
        <section class="panel-operaciones">
          <h2>Envíos recientes</h2>
          <?php if (!$envios): ?><p>Todavía no hay envíos activos.</p><?php endif; ?>
          <div class="lista-envios-operaciones">
            <?php foreach ($envios as $envio): ?>
              <a class="elemento-envio-operaciones" href="panel-admin.php?codigo=<?= urlencode($envio['codigo_envio']) ?>">
                <strong><?= limpiar($envio['codigo_envio']) ?> · <?= limpiar(estadoLegible($envio['estado'])) ?></strong>
                <span>Ubicación: <?= limpiar($envio['ubicacion'] ?: 'Sin movimientos cargados') ?></span>
                <span>Destino: <?= limpiar($envio['destino']) ?></span>
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
