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
require_once __DIR__ . '/../app/dao/SucursalDAO.php';
protegerCliente();
$usuario = usuarioActual();
$error = '';
$idZona = null;
$cliente = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'solicitar') {
    $remitente = trim($_POST['remitente'] ?? '');
    $origen = trim($_POST['origen'] ?? '');
    $destinatario = trim($_POST['destinatario'] ?? '');
    $destino = trim($_POST['destino'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $emailRemitente = trim($_POST['email_remitente'] ?? '');
    $emailDestinatario = trim($_POST['email_destinatario'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $peso = filter_var($_POST['peso'] ?? null, FILTER_VALIDATE_FLOAT);
    $largo = trim($_POST['largo'] ?? '') === '' ? null : filter_var($_POST['largo'], FILTER_VALIDATE_FLOAT);
    $ancho = trim($_POST['ancho'] ?? '') === '' ? null : filter_var($_POST['ancho'], FILTER_VALIDATE_FLOAT);
    $alto = trim($_POST['alto'] ?? '') === '' ? null : filter_var($_POST['alto'], FILTER_VALIDATE_FLOAT);
    $valorDeclarado = trim($_POST['valor_declarado'] ?? '') === '' ? null : filter_var($_POST['valor_declarado'], FILTER_VALIDATE_FLOAT);
    $idZona = filter_var($_POST['zona'] ?? null, FILTER_VALIDATE_INT);
    $fragil = isset($_POST['fragil']);

    $dimensionesInvalidas = ($largo === false || $ancho === false || $alto === false)
        || ((($largo === null) + ($ancho === null) + ($alto === null)) !== 0
            && (($largo === null) + ($ancho === null) + ($alto === null)) !== 3)
        || ($largo !== null && ($largo <= 0 || $ancho <= 0 || $alto <= 0));
    $emailsInvalidos = ($emailRemitente !== '' && !filter_var($emailRemitente, FILTER_VALIDATE_EMAIL))
        || ($emailDestinatario !== '' && !filter_var($emailDestinatario, FILTER_VALIDATE_EMAIL));
    if (!$remitente || !$origen || !$destinatario || !$destino || !$descripcion || $peso === false || $peso <= 0 || !$idZona
        || $dimensionesInvalidas || $emailsInvalidos || $valorDeclarado === false || ($valorDeclarado !== null && $valorDeclarado < 0)) {
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
                $tarifa = $tarifaDAO->obtenerVigente((int) $idZona, 'ESTANDAR');
                $sucursalInicial = (new SucursalDAO($db))->obtenerPrimeraPorZona((int) $idZona);
                if (!$tarifa) {
                    $error = t('La zona elegida todavía no tiene una tarifa vigente. Contactanos para solicitarla.');
                } elseif (!$sucursalInicial) {
                    $error = t('La zona elegida todavía no tiene una sucursal habilitada.');
                } else {
                    $volumenM3 = $largo !== null ? ((float) $largo * (float) $ancho * (float) $alto) / 1000000 : 0;
                    $precioBase = (float) $tarifa['precio_base']
                        + ((float) $tarifa['precio_por_kg'] * (float) $peso)
                        + ((float) $tarifa['precio_por_m3'] * $volumenM3)
                        + ((float) $tarifa['recargo_fragil'] * (int) $fragil);
                    $descuentoAprobado = $cliente['tipo_cliente'] === 'CORPORATIVO'
                        && $cliente['estado_validacion'] === 'VALIDADO'
                        && (bool) $cliente['aprobado_corporativo'];
                    $porcentajeDescuento = $descuentoAprobado ? (float) $cliente['descuento_porcentaje'] : 0;
                    $precio = round($precioBase * (1 - $porcentajeDescuento / 100), 2);

                    $db->beginTransaction();
                    $codigoPaquete = 'DEL-' . strtoupper(bin2hex(random_bytes(4)));
                    $idPaquete = $paqueteDAO->crear($codigoPaquete, $descripcion, (float) $peso,
                        $largo === null ? null : (float) $largo,
                        $ancho === null ? null : (float) $ancho,
                        $alto === null ? null : (float) $alto,
                        'Mercadería general', $valorDeclarado === null ? null : (float) $valorDeclarado, $fragil);
                    $idRemitente = $contactoDAO->crear('REMITENTE', $remitente, $telefono ?: null,
                        $emailRemitente ?: null, $origen, null, (int) $cliente['id_cliente']);
                    $idDestinatario = $contactoDAO->crear('DESTINATARIO', $destinatario, $telefono ?: null,
                        $emailDestinatario ?: null, $destino, (int) $idZona, null);
                    $codigoEnvio = 'ENV-' . strtoupper(bin2hex(random_bytes(4)));
                    $fechaEstimada = date('Y-m-d', strtotime('+3 days'));
                    $idEnvio = $envioDAO->crear($codigoEnvio, (int) $cliente['id_cliente'], $idRemitente,
                        $idDestinatario, $idPaquete, (int) $idZona, (int) $tarifa['id_tarifa'],
                        $precio, $fechaEstimada, $usuario['id_usuario']);
                    $movimientoDAO->crear($idEnvio, (int) $sucursalInicial['id_sucursal'],
                        'Solicitud recibida. El paquete espera su recepción en sucursal.');
                    $db->commit();
                    $_SESSION['mensaje'] = t('Solicitud registrada con el código ') . $codigoEnvio
                        . '. ' . t('Tarifa: ') . $tarifa['nombre'] . ' · $' . number_format($precio, 2, ',', '.') . '.';
                    header('Location: envios.php?codigo=' . urlencode($codigoEnvio));
                    exit;
                }
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
    $cliente = (new ClienteDAO())->obtenerPorUsuario($usuario['id_usuario']);
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

    <section class="perfil-cliente-cuenta" aria-labelledby="profile-title">
      <h2 id="profile-title"><?= limpiar(t('Datos de tu cuenta')) ?></h2>
      <p><?= limpiar(t('Tipo de cliente:')) ?> <?= limpiar(t($cliente['tipo_cliente'] ?? 'PARTICULAR')) ?></p>
      <?php if (!empty($cliente['razon_social'])): ?><p><?= limpiar(t('Razón social:')) ?> <?= limpiar($cliente['razon_social']) ?></p><?php endif; ?>
      <?php if (!empty($cliente['cuit_dni'])): ?><p><?= limpiar(t('CUIT/DNI:')) ?> <?= limpiar($cliente['cuit_dni']) ?></p><?php endif; ?>
      <?php if (!empty($cliente['telefono'])): ?><p><?= limpiar(t('Teléfono:')) ?> <?= limpiar($cliente['telefono']) ?></p><?php endif; ?>
      <?php if (!empty($cliente['direccion'])): ?><p><?= limpiar(t('Dirección:')) ?> <?= limpiar($cliente['direccion']) ?></p><?php endif; ?>
      <p><?= limpiar(t('Cuenta registrada:')) ?> <?= limpiar($cliente['fecha_registro'] ?? '') ?></p>
      <?php if (($cliente['tipo_cliente'] ?? '') === 'CORPORATIVO'): ?>
        <p><?= limpiar(t('Estado corporativo:')) ?> <?= limpiar(t($cliente['estado_validacion'])) ?>
          <?php if ($cliente['estado_validacion'] === 'VALIDADO' && $cliente['aprobado_corporativo']): ?>
            · <?= limpiar(t('Descuento aprobado:')) ?> <?= limpiar($cliente['descuento_porcentaje']) ?>%
          <?php endif; ?>
        </p>
      <?php endif; ?>
    </section>

    <section class="solicitud-cuenta" aria-labelledby="request-title">
      <h2 id="request-title"><?= limpiar(t('Solicitar un envío')) ?></h2>
      <p><?= limpiar(t('Completá los datos para registrar un envío dentro de la provincia de Buenos Aires.')) ?></p>
      <form method="post" class="formulario-solicitud-envio">
        <input type="hidden" name="accion" value="solicitar">
        <label><?= limpiar(t('Nombre del remitente')) ?><input name="remitente" autocomplete="name" required maxlength="120" value="<?= limpiar($_POST['remitente'] ?? '') ?>"></label>
        <label><?= limpiar(t('Origen · localidad y provincia')) ?><input name="origen" placeholder="<?= limpiar(t('Olivos, Buenos Aires')) ?>" required maxlength="200" value="<?= limpiar($_POST['origen'] ?? '') ?>"></label>
        <label><?= limpiar(t('Email del remitente (opcional)')) ?><input name="email_remitente" type="email" maxlength="120" value="<?= limpiar($_POST['email_remitente'] ?? '') ?>"></label>
        <label><?= limpiar(t('Nombre del destinatario')) ?><input name="destinatario" required maxlength="120" value="<?= limpiar($_POST['destinatario'] ?? '') ?>"></label>
        <label><?= limpiar(t('Destino · localidad y provincia')) ?><input name="destino" placeholder="<?= limpiar(t('San Martín, Buenos Aires')) ?>" required maxlength="200" value="<?= limpiar($_POST['destino'] ?? '') ?>"></label>
        <label><?= limpiar(t('Email del destinatario (opcional)')) ?><input name="email_destinatario" type="email" maxlength="120" value="<?= limpiar($_POST['email_destinatario'] ?? '') ?>"></label>
        <label><?= limpiar(t('Teléfono de contacto')) ?><input name="telefono" type="tel" maxlength="30" value="<?= limpiar($_POST['telefono'] ?? '') ?>"></label>
        <label><?= limpiar(t('Zona de entrega')) ?><select name="zona" required><option value=""><?= limpiar(t('Elegí una zona')) ?></option>
          <?php foreach ($zonas as $zona): ?><option value="<?= (int) $zona['id_zona'] ?>" <?= (string) ($idZona ?? '') === (string) $zona['id_zona'] ? 'selected' : '' ?>><?= limpiar($zona['nombre']) ?></option><?php endforeach; ?>
        </select></label>
        <label><?= limpiar(t('Descripción del paquete')) ?><input name="descripcion" required maxlength="255" value="<?= limpiar($_POST['descripcion'] ?? '') ?>"></label>
        <label><?= limpiar(t('Peso (kg)')) ?><input name="peso" type="number" min="0.1" max="3000" step="0.1" required value="<?= limpiar($_POST['peso'] ?? '') ?>"></label>
        <label><?= limpiar(t('Largo (cm, opcional)')) ?><input name="largo" type="number" min="0.1" step="0.1" value="<?= limpiar($_POST['largo'] ?? '') ?>"></label>
        <label><?= limpiar(t('Ancho (cm, opcional)')) ?><input name="ancho" type="number" min="0.1" step="0.1" value="<?= limpiar($_POST['ancho'] ?? '') ?>"></label>
        <label><?= limpiar(t('Alto (cm, opcional)')) ?><input name="alto" type="number" min="0.1" step="0.1" value="<?= limpiar($_POST['alto'] ?? '') ?>"></label>
        <label><?= limpiar(t('Valor declarado (opcional)')) ?><input name="valor_declarado" type="number" min="0" step="0.01" value="<?= limpiar($_POST['valor_declarado'] ?? '') ?>"></label>
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
          <p><?= limpiar(t('Precio calculado:')) ?> <?= limpiar($envio['precio_final'] === null ? t('A confirmar') : '$' . number_format((float) $envio['precio_final'], 2, ',', '.')) ?></p>
          <a class="enlace-texto" href="seguimiento.php?codigo=<?= urlencode($envio['codigo_envio']) ?>"><?= limpiar(t('Ver seguimiento →')) ?></a>
        </article>
      <?php endforeach; ?>
    </section>
</main>
  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
