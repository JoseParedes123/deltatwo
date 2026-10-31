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
        $error = t('Completá los datos del envío y verificá el peso.');
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
                $error = t('La cuenta no tiene un perfil de cliente asociado.');
            } elseif (!$zona || !$zona['activo']) {
                $error = t('Elegí una zona de entrega disponible.');
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
                $_SESSION['mensaje'] = t('Solicitud registrada con el código ') . $codigoEnvio . '.';
                header('Location: envios.php?codigo=' . urlencode($codigoEnvio));
                exit;
            }
        } catch (Throwable $exception) {
            if ($db && $db->inTransaction()) $db->rollBack();
            error_log($exception->getMessage());
            $error = t('No pudimos registrar el envío. Verificá la base de datos e intentá de nuevo.');
        }
    }
}

$mensaje = mensajeFlasheado() ?? '';
// carga las zonas disponibles y los envíos asociados a la cuenta.
try {
    $envios = (new EnvioDAO())->obtenerPorUsuario($usuario['id_usuario']);
    $zonas = (new ZonaDAO())->obtenerTodos();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $envios = [];
    $zonas = [];
    $error = t('No pudimos cargar los datos de tu cuenta. Intentá recargar la página.');
}
?>
<!doctype html>
<html lang="<?= limpiar(idiomaActual()) ?>">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="media/favicon.png">
  <meta name="theme-color" content="#073486"><title><?= limpiar(t('Mis envíos')) ?> | Logística Delta</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include __DIR__ . '/partials/nav.php'; ?>
  <main class="contenedor pagina-cuenta">
    <!-- el cliente puede solicitar un envío y consultar los que ya tiene asociados. -->
    <div class="titulo-cinta"><h1><?= limpiar(t('Mis envíos')) ?></h1></div>
    <p class="introduccion-pagina"><?= limpiar(t('Hola, ')) ?><?= limpiar($usuario['nombre']) ?><?= limpiar(t('. Acá podés solicitar y consultar tus envíos.')) ?></p>
    <?php if ($mensaje): ?><p class="respuesta-operaciones" role="status"><?= limpiar($mensaje) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="respuesta-operaciones" role="alert"><?= limpiar($error) ?></p><?php endif; ?>

    <section class="solicitud-cuenta" aria-labelledby="request-title">
      <h2 id="request-title"><?= limpiar(t('Solicitar un envío')) ?></h2>
      <p><?= limpiar(t('Completá los datos para registrar un envío dentro de la provincia de Buenos Aires.')) ?></p>
      <form method="post" class="formulario-solicitud-envio">
        <input type="hidden" name="accion" value="solicitar">
        <label><?= limpiar(t('Nombre del remitente')) ?><input name="remitente" autocomplete="name" required maxlength="120" value="<?= limpiar($_POST['remitente'] ?? '') ?>"></label>
        <label><?= limpiar(t('Origen · localidad y provincia')) ?><input name="origen" placeholder="<?= limpiar(t('Olivos, Buenos Aires')) ?>" required maxlength="200" value="<?= limpiar($_POST['origen'] ?? '') ?>"></label>
        <label><?= limpiar(t('Nombre del destinatario')) ?><input name="destinatario" required maxlength="120" value="<?= limpiar($_POST['destinatario'] ?? '') ?>"></label>
        <label><?= limpiar(t('Destino · localidad y provincia')) ?><input name="destino" placeholder="<?= limpiar(t('San Martín, Buenos Aires')) ?>" required maxlength="200" value="<?= limpiar($_POST['destino'] ?? '') ?>"></label>
        <label><?= limpiar(t('Teléfono de contacto')) ?><input name="telefono" type="tel" maxlength="30" value="<?= limpiar($_POST['telefono'] ?? '') ?>"></label>
        <label><?= limpiar(t('Zona de entrega')) ?><select name="zona" required><option value=""><?= limpiar(t('Elegí una zona')) ?></option>
          <?php foreach ($zonas as $zona): ?><option value="<?= (int) $zona['id_zona'] ?>" <?= (string) ($idZona ?? '') === (string) $zona['id_zona'] ? 'selected' : '' ?>><?= limpiar($zona['nombre']) ?></option><?php endforeach; ?>
        </select></label>
        <label><?= limpiar(t('Descripción del paquete')) ?><input name="descripcion" required maxlength="255" value="<?= limpiar($_POST['descripcion'] ?? '') ?>"></label>
        <label><?= limpiar(t('Peso (kg)')) ?><input name="peso" type="number" min="0.1" max="3000" step="0.1" required value="<?= limpiar($_POST['peso'] ?? '') ?>"></label>
        <label class="opcion-fragil"><input name="fragil" type="checkbox" <?= isset($_POST['fragil']) ? 'checked' : '' ?>> <?= limpiar(t('Paquete frágil')) ?></label>
        <button class="boton-principal" type="submit"><?= limpiar(t('Solicitar envío')) ?></button>
      </form>
    </section>

    <section class="envios-cliente" aria-label="<?= limpiar(t('Tus envíos')) ?>">
      <?php if (!$envios): ?><p><?= limpiar(t('Todavía no tenés envíos asociados a tu cuenta.')) ?></p><?php endif; ?>
      <?php foreach ($envios as $envio): ?>
        <article class="tarjeta-envio-cliente">
          <h2><?= limpiar($envio['codigo_envio']) ?></h2>
          <p><?= limpiar(t('Estado:')) ?> <?= limpiar(estadoLegible($envio['estado'])) ?></p>
          <p><?= limpiar(t('Destino:')) ?> <?= limpiar($envio['destino']) ?> · <?= limpiar($envio['destinatario']) ?></p>
          <p><?= limpiar(t('Entrega estimada:')) ?> <?= limpiar($envio['fecha_entrega_estimada'] ?: t('A confirmar')) ?></p>
          <a class="enlace-texto" href="seguimiento.php?codigo=<?= urlencode($envio['codigo_envio']) ?>"><?= limpiar(t('Ver seguimiento →')) ?></a>
        </article>
      <?php endforeach; ?>
    </section>
</main>
  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
