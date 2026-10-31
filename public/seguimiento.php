<?php
$paginaActual = 'seguimiento';
require_once __DIR__ . '/../app/helpers/funciones.php';
require_once __DIR__ . '/../app/dao/EnvioDAO.php';
require_once __DIR__ . '/../app/dao/MovimientoSeguimientoDAO.php';
iniciarSesion();

$codigo = strtoupper(trim($_POST['codigo'] ?? $_GET['codigo'] ?? ''));
$envio = null;
$movimientos = [];
$error = '';

// la consulta pública usa el código del envío; no requiere una cuenta de cliente.
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['codigo'])) {
    if (!preg_match('/^[A-Z0-9-]{4,20}$/', $codigo)) {
        $error = t('Ingresá un código válido para buscar el paquete.');
    } else {
        try {
            $envio = (new EnvioDAO())->obtenerParaSeguimiento($codigo);
            if (!$envio) {
                $error = t('No encontramos un envío con ese código. Revisá que esté bien escrito.');
            } else {
                $movimientos = (new MovimientoSeguimientoDAO())->obtenerPorEnvio((int) $envio['id_envio']);
            }
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $error = t('No pudimos consultar el envío en este momento. Intentá nuevamente más tarde.');
        }
    }
}

$etapasPorEstado = [
    'REGISTRADO' => 0, 'RECIBIDO_DEPOSITO' => 1, 'PREPARADO' => 2,
    'EN_TRANSITO' => 3, 'EN_REPARTO' => 3, 'ENTREGADO' => 4,
];
$etapaActual = $envio ? ($etapasPorEstado[$envio['estado']] ?? -1) : -1;
$avisosEstado = [
    'NO_ENTREGADO' => t('No se pudo completar la entrega. Contactanos para coordinar los próximos pasos.'),
    'DEVUELTO' => t('El paquete fue devuelto al remitente. Contactanos si necesitás más información.'),
    'EXTRAVIADO' => t('Estamos revisando una incidencia con este envío. Contactanos para recibir asistencia.'),
    'CANCELADO' => t('Este envío fue cancelado. Contactanos si tenés alguna consulta.'),
];
?>
<!doctype html>
<html lang="<?= limpiar(idiomaActual()) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="media/favicon.png">
  <meta name="theme-color" content="#073486">
  <title><?= limpiar(t('Seguimiento de envíos')) ?> | Logística Delta</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include __DIR__ . '/partials/nav.php'; ?>
  <main class="contenedor pagina-seguimiento">
    <!-- debajo del formulario se muestran el estado y los movimientos encontrados. -->
    <h1><?= limpiar(t('Seguimiento de envíos')) ?></h1>
    <p class="bajada"><?= limpiar(t('Ingresá el código de tu envío para conocer su estado y dónde está ubicado.')) ?></p>
    <form class="formulario-seguimiento" method="get">
      <input name="codigo" aria-label="<?= limpiar(t('Código de envío')) ?>" placeholder="<?= limpiar(t('Ej.: DEL-0001 o ENV-0001')) ?>" autocomplete="off" required value="<?= limpiar($codigo) ?>">
      <button type="submit"><?= limpiar(t('Seguir paquete')) ?></button>
    </form>
    <?php if ($error): ?><p class="mensaje-seguimiento" role="status"><?= limpiar($error) ?></p><?php endif; ?>

    <?php if ($envio): ?>
      <?php if (isset($avisosEstado[$envio['estado']])): ?>
        <p class="aviso-estado-envio" role="status"><?= limpiar($avisosEstado[$envio['estado']]) ?></p>
      <?php else: ?>
      <div class="progreso-envio" aria-label="<?= limpiar(t('Etapas del envío')) ?>">
        <?php foreach (['Registrado', 'En depósito', 'En preparación', 'En tránsito', 'Entregado'] as $indice => $etapa): ?>
          <div class="paso-progreso <?= $indice <= $etapaActual ? 'actual' : '' ?>"><span class="punto-progreso"></span><span><?= limpiar(t($etapa)) ?></span></div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <section class="tarjeta-envio" aria-label="<?= limpiar(t('Detalles del envío')) ?>">
        <div class="resumen-envio">
          <div class="encabezado-envio"><h2><?= limpiar($envio['codigo_envio']) ?></h2><span class="etiqueta-estado"><?= limpiar(estadoLegible($envio['estado'])) ?></span></div>
          <p><?= limpiar(t('Origen:')) ?> <?= limpiar($envio['origen']) ?></p>
          <p><?= limpiar(t('Destino:')) ?> <?= limpiar($envio['destino']) ?></p>
          <p><?= limpiar(t('Fecha de solicitud:')) ?> <?= limpiar($envio['fecha_solicitud']) ?></p>
          <p><?= limpiar(t('Entrega estimada:')) ?> <?= limpiar($envio['fecha_entrega_estimada'] ?: t('A confirmar')) ?></p>
        </div>
        <div class="contenedor-recorrido">
          <h3><?= limpiar(t('Recorrido por sucursales')) ?></h3>
          <ol class="recorrido-envio">
            <?php if ($movimientos): ?>
              <?php foreach ($movimientos as $movimiento): ?>
                <li class="elemento-recorrido">
                  <strong><?= limpiar($movimiento['sucursal']) ?></strong>
                  <span><?= limpiar($movimiento['detalle']) ?> · <?= limpiar($movimiento['provincia']) ?></span>
                  <time datetime="<?= limpiar($movimiento['fecha_hora']) ?>"><?= limpiar(date('d/m/Y H:i', strtotime($movimiento['fecha_hora']))) ?></time>
                </li>
              <?php endforeach; ?>
            <?php else: ?>
              <li class="elemento-recorrido"><?= limpiar(t('Todavía no hay sucursales registradas para este paquete.')) ?></li>
            <?php endif; ?>
          </ol>
        </div>
        <div>
          <h3><?= limpiar(t('Detalles del paquete')) ?></h3>
          <p><?= limpiar(t('Descripción:')) ?> <?= limpiar($envio['descripcion_paquete'] ?: t('Sin descripción')) ?></p>
          <p><?= limpiar(t('Peso:')) ?> <?= limpiar($envio['peso_kg'] ? $envio['peso_kg'] . ' kg' : t('No informado')) ?></p>
        </div>
      </section>
    <?php endif; ?>
  </main>
  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
