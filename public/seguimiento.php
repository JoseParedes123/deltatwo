<?php
$paginaActual = 'seguimiento';
require_once __DIR__ . '/../app/helpers/funciones.php';
require_once __DIR__ . '/../app/dao/EnvioDAO.php';
require_once __DIR__ . '/../app/dao/MovimientoSeguimientoDAO.php';

$codigo = strtoupper(trim($_POST['codigo'] ?? $_GET['codigo'] ?? ''));
$envio = null;
$movimientos = [];
$error = '';

// La consulta pública usa el código del envío; no requiere una cuenta de cliente.
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['codigo'])) {
    if (!preg_match('/^[A-Z0-9-]{4,20}$/', $codigo)) {
        $error = 'Ingresá un código válido para buscar el paquete.';
    } else {
        try {
            $envio = (new EnvioDAO())->obtenerParaSeguimiento($codigo);
            if (!$envio) {
                $error = 'No encontramos un envío con ese código. Revisá que esté bien escrito.';
            } else {
                $movimientos = (new MovimientoSeguimientoDAO())->obtenerPorEnvio((int) $envio['id_envio']);
            }
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $error = 'No pudimos consultar el envío. Verificá que MySQL esté activo.';
        }
    }
}

$etapasPorEstado = [
    'REGISTRADO' => 0, 'RECIBIDO_DEPOSITO' => 1, 'PREPARADO' => 2,
    'EN_TRANSITO' => 3, 'EN_REPARTO' => 3, 'ENTREGADO' => 4,
    'NO_ENTREGADO' => 3, 'DEVUELTO' => 3, 'EXTRAVIADO' => 3,
];
$etapaActual = $envio ? ($etapasPorEstado[$envio['estado']] ?? -1) : -1;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#073486">
  <title>Seguimiento de envíos | Logística Delta</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include __DIR__ . '/partials/nav.php'; ?>
  <main class="contenedor pagina-seguimiento">
    <h1>Seguimiento de envíos</h1>
    <p class="bajada">Ingresá el código de tu envío para conocer su estado y dónde está ubicado.</p>
    <form class="formulario-seguimiento" method="get">
      <input name="codigo" aria-label="Código de envío" placeholder="Ej.: DEL-0001 o ENV-0001" autocomplete="off" required value="<?= limpiar($codigo) ?>">
      <button type="submit">Seguir paquete</button>
    </form>
    <?php if ($error): ?><p class="mensaje-seguimiento" role="status"><?= limpiar($error) ?></p><?php endif; ?>

    <?php if ($envio): ?>
      <div class="progreso-envio" aria-label="Etapas del envío">
        <?php foreach (['Registrado', 'En depósito', 'En preparación', 'En tránsito', 'Entregado'] as $indice => $etapa): ?>
          <div class="paso-progreso <?= $indice <= $etapaActual ? 'actual' : '' ?>"><span class="punto-progreso"></span><span><?= limpiar($etapa) ?></span></div>
        <?php endforeach; ?>
      </div>
      <section class="tarjeta-envio" aria-label="Detalles del envío">
        <div class="resumen-envio">
          <div class="encabezado-envio"><h2><?= limpiar($envio['codigo_envio']) ?></h2><span class="etiqueta-estado"><?= limpiar(estadoLegible($envio['estado'])) ?></span></div>
          <p>Origen: <?= limpiar($envio['origen']) ?></p>
          <p>Destino: <?= limpiar($envio['destino']) ?></p>
          <p>Fecha de solicitud: <?= limpiar($envio['fecha_solicitud']) ?></p>
          <p>Entrega estimada: <?= limpiar($envio['fecha_entrega_estimada'] ?: 'A confirmar') ?></p>
        </div>
        <div class="contenedor-recorrido">
          <h3>Recorrido por sucursales</h3>
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
              <li class="elemento-recorrido">Todavía no hay sucursales registradas para este paquete.</li>
            <?php endif; ?>
          </ol>
        </div>
        <div>
          <h3>Detalles del paquete</h3>
          <p>Descripción: <?= limpiar($envio['descripcion_paquete'] ?: 'Sin descripción') ?></p>
          <p>Peso: <?= limpiar($envio['peso_kg'] ? $envio['peso_kg'] . ' kg' : 'No informado') ?></p>
        </div>
      </section>
    <?php endif; ?>
  </main>
  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
