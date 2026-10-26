<?php
require_once __DIR__ . '/../app/helpers/funciones.php';
require_once __DIR__ . '/../app/config/Conexion.php';
require_once __DIR__ . '/../app/dao/ClienteDAO.php';
require_once __DIR__ . '/../app/dao/ZonaDAO.php';
require_once __DIR__ . '/../app/dao/TarifaDAO.php';
require_once __DIR__ . '/../app/dao/PaqueteDAO.php';
require_once __DIR__ . '/../app/dao/ContactoDAO.php';
require_once __DIR__ . '/../app/dao/EnvioDAO.php';
require_once __DIR__ . '/../app/dao/MovimientoSeguimientoDAO.php';
protegerCliente();
$usuario = usuarioActual();
$error = '';
$idZona = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'solicitar') {
    $remitente = trim($_POST['remitente'] ?? '');
    $origen = trim($_POST['origen'] ?? '');
    $destinatario = trim($_POST['destinatario'] ?? '');
    $destino = trim($_POST['destino'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $peso = filter_var($_POST['peso'] ?? null, FILTER_VALIDATE_FLOAT);
    $idZona = filter_var($_POST['zona'] ?? null, FILTER_VALIDATE_INT);
    $fragil = isset($_POST['fragil']);

    if (!$remitente || !$origen || !$destinatario || !$destino || !$descripcion || $peso === false || $peso <= 0 || !$idZona) {
        $error = 'Completá los datos del envío y verificá el peso.';
    } else {
        $db = null;
        try {
            $db = (new Conexion())->conectar();
            $clienteDAO = new ClienteDAO($db);
            $zonaDAO = new ZonaDAO($db);
            $tarifaDAO = new TarifaDAO($db);
            $paqueteDAO = new PaqueteDAO($db);
            $contactoDAO = new ContactoDAO($db);
            $envioDAO = new EnvioDAO($db);
            $movimientoDAO = new MovimientoSeguimientoDAO($db);

            $cliente = $clienteDAO->obtenerPorUsuario($usuario['id_usuario']);
            $zona = $zonaDAO->obtenerPorId((int) $idZona);
            if (!$cliente) {
                $error = 'La cuenta no tiene un perfil de cliente asociado.';
            } elseif (!$zona || !$zona['activo']) {
                $error = 'Elegí una zona de entrega disponible.';
            } else {
                $db->beginTransaction();
                $tarifa = $tarifaDAO->obtenerVigente((int) $idZona, 'ESTANDAR');
                $precio = $tarifa
                    ? (float) $tarifa['precio_base'] + ((float) $tarifa['precio_por_kg'] * $peso) + ((float) $tarifa['recargo_fragil'] * (int) $fragil)
                    : null;
                $codigoPaquete = 'DEL-' . strtoupper(bin2hex(random_bytes(4)));
                $idPaquete = $paqueteDAO->crear($codigoPaquete, $descripcion, (float) $peso, null, null, null, 1, 'Mercadería general', null, $fragil);
                $idRemitente = $contactoDAO->crear('REMITENTE', $remitente, $telefono ?: null, null, $origen, null, $cliente['id_cliente']);
                $idDestinatario = $contactoDAO->crear('DESTINATARIO', $destinatario, $telefono ?: null, null, $destino, (int) $idZona, null);
                $codigoEnvio = 'ENV-' . strtoupper(bin2hex(random_bytes(4)));
                $fechaEstimada = date('Y-m-d', strtotime('+3 days'));
                $idEnvio = $envioDAO->crear($codigoEnvio, (int) $cliente['id_cliente'], $idRemitente, $idDestinatario, $idPaquete, (int) $idZona, $tarifa ? (int) $tarifa['id_tarifa'] : null, $precio, $fechaEstimada, $usuario['id_usuario']);
                $movimientoDAO->crear($idEnvio, 'Centro de recepción Logística Delta', $zona['nombre'], 'Solicitud recibida. El paquete espera su recepción en sucursal.');
                $db->commit();
                $_SESSION['mensaje'] = "Solicitud registrada con el código {$codigoEnvio}.";
                header('Location: envios.php?codigo=' . urlencode($codigoEnvio));
                exit;
            }
        } catch (Throwable $exception) {
            if ($db && $db->inTransaction()) $db->rollBack();
            error_log($exception->getMessage());
            $error = 'No pudimos registrar el envío. Verificá la base de datos e intentá de nuevo.';
        }
    }
}

$mensaje = mensajeFlasheado() ?? '';
// Carga las zonas disponibles y los envíos asociados a la cuenta.
try {
    $envios = (new EnvioDAO())->obtenerPorUsuario($usuario['id_usuario']);
    $zonas = (new ZonaDAO())->obtenerTodos();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $envios = [];
    $zonas = [];
    $error = 'No pudimos cargar los datos de tu cuenta. Intentá recargar la página.';
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#073486"><title>Mis envíos | Logística Delta</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include __DIR__ . '/partials/nav.php'; ?>
  <main class="contenedor pagina-cuenta">
    <div class="titulo-cinta"><h1>Mis envíos</h1></div>
    <p class="introduccion-pagina">Hola, <?= limpiar($usuario['nombre']) ?>. Acá podés solicitar y consultar tus envíos.</p>
    <?php if ($mensaje): ?><p class="respuesta-operaciones" role="status"><?= limpiar($mensaje) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="respuesta-operaciones" role="alert"><?= limpiar($error) ?></p><?php endif; ?>

    <section class="solicitud-cuenta" aria-labelledby="request-title">
      <h2 id="request-title">Solicitar un envío</h2>
      <p>Completá los datos para registrar un envío dentro de la provincia de Buenos Aires.</p>
      <form method="post" class="formulario-solicitud-envio">
        <input type="hidden" name="accion" value="solicitar">
        <label>Nombre del remitente<input name="remitente" autocomplete="name" required maxlength="120" value="<?= limpiar($_POST['remitente'] ?? '') ?>"></label>
        <label>Origen · localidad y provincia<input name="origen" placeholder="Olivos, Buenos Aires" required maxlength="200" value="<?= limpiar($_POST['origen'] ?? '') ?>"></label>
        <label>Nombre del destinatario<input name="destinatario" required maxlength="120" value="<?= limpiar($_POST['destinatario'] ?? '') ?>"></label>
        <label>Destino · localidad y provincia<input name="destino" placeholder="San Martín, Buenos Aires" required maxlength="200" value="<?= limpiar($_POST['destino'] ?? '') ?>"></label>
        <label>Teléfono de contacto<input name="telefono" type="tel" maxlength="30" value="<?= limpiar($_POST['telefono'] ?? '') ?>"></label>
        <label>Zona de entrega<select name="zona" required><option value="">Elegí una zona</option>
          <?php foreach ($zonas as $zona): ?><option value="<?= (int) $zona['id_zona'] ?>" <?= (string) ($idZona ?? '') === (string) $zona['id_zona'] ? 'selected' : '' ?>><?= limpiar($zona['nombre']) ?></option><?php endforeach; ?>
        </select></label>
        <label>Descripción del paquete<input name="descripcion" required maxlength="255" value="<?= limpiar($_POST['descripcion'] ?? '') ?>"></label>
        <label>Peso (kg)<input name="peso" type="number" min="0.1" max="3000" step="0.1" required value="<?= limpiar($_POST['peso'] ?? '') ?>"></label>
        <label class="opcion-fragil"><input name="fragil" type="checkbox" <?= isset($_POST['fragil']) ? 'checked' : '' ?>> Paquete frágil</label>
        <button class="boton-principal" type="submit">Solicitar envío</button>
      </form>
    </section>

    <section class="envios-cliente" aria-label="Tus envíos">
      <?php if (!$envios): ?><p>Todavía no tenés envíos asociados a tu cuenta.</p><?php endif; ?>
      <?php foreach ($envios as $envio): ?>
        <article class="tarjeta-envio-cliente">
          <h2><?= limpiar($envio['codigo_envio']) ?></h2>
          <p>Estado: <?= limpiar(estadoLegible($envio['estado'])) ?></p>
          <p>Destino: <?= limpiar($envio['destino']) ?> · <?= limpiar($envio['destinatario']) ?></p>
          <p>Entrega estimada: <?= limpiar($envio['fecha_entrega_estimada'] ?: 'A confirmar') ?></p>
          <a class="enlace-texto" href="seguimiento.php?codigo=<?= urlencode($envio['codigo_envio']) ?>">Ver seguimiento →</a>
        </article>
      <?php endforeach; ?>
    </section>
    <p class="enlace-seguimiento-cuenta"><a class="boton-principal" href="seguimiento.php">Seguir un envío sin iniciar sesión</a></p>
  </main>
  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
