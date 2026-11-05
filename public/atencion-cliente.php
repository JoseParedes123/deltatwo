<?php
require_once __DIR__ . '/../app/helpers/funciones.php';
require_once __DIR__ . '/../app/dao/ConsultaWebDAO.php';
iniciarSesion();
protegerAtencionCliente();

$usuario = usuarioActual();
$dao = new ConsultaWebDAO();
$error = '';
$mensaje = mensajeFlasheado() ?? '';
$filtro = $_GET['estado'] ?? 'TODAS';
$filtrosValidos = ['TODAS', 'PENDIENTE', 'RESPUESTA_PREPARADA', 'RESPONDIDA'];
if (!in_array($filtro, $filtrosValidos, true)) $filtro = 'TODAS';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $idConsulta = filter_var($_POST['id_consulta'] ?? null, FILTER_VALIDATE_INT);
    try {
        if (!$idConsulta) {
            throw new InvalidArgumentException('Seleccioná una consulta válida.');
        }
        $consulta = $dao->obtenerPorId($idConsulta);
        if (!$consulta) {
            throw new InvalidArgumentException('La consulta ya no está disponible.');
        }

        if ($accion === 'preparar') {
            $respuesta = trim($_POST['respuesta'] ?? '');
            if ($consulta['estado'] === 'RESPONDIDA') {
                throw new InvalidArgumentException('Esta consulta ya figura como respondida.');
            }
            if ($respuesta === '' || mb_strlen($respuesta) > 3000) {
                throw new InvalidArgumentException('Escribí una respuesta de hasta 3000 caracteres.');
            }
            if (!$dao->prepararRespuesta($idConsulta, $usuario['id_usuario'], $respuesta)) {
                throw new LogicException('No se pudo guardar la respuesta.');
            }
            $_SESSION['mensaje'] = 'Respuesta guardada. Revisala y enviála desde tu correo.';
            header('Location: atencion-cliente.php?estado=RESPUESTA_PREPARADA&correo=' . $idConsulta);
            exit;
        }

        if ($accion === 'marcar_respondida' && $dao->marcarRespondida($idConsulta)) {
            $_SESSION['mensaje'] = 'La consulta quedó marcada como respondida.';
            header('Location: atencion-cliente.php?estado=RESPONDIDA');
            exit;
        }
        throw new InvalidArgumentException('La acción no es válida o la respuesta todavía no está preparada.');
    } catch (Throwable $exception) {
        $error = $exception instanceof InvalidArgumentException || $exception instanceof LogicException
            ? $exception->getMessage()
            : 'No pudimos guardar el cambio. Intentá nuevamente.';
        if (!($exception instanceof InvalidArgumentException) && !($exception instanceof LogicException)) {
            error_log($exception->getMessage());
        }
    }
}

try {
    $consultas = $dao->obtenerBandeja($filtro);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $consultas = [];
    $error = 'No pudimos cargar la bandeja. Verificá que esté aplicada la actualización de la base de datos.';
}

function etiquetaEstadoConsulta(string $estado): string
{
    return match ($estado) {
        'RESPUESTA_PREPARADA' => 'Respuesta preparada',
        'RESPONDIDA' => 'Respondida',
        default => 'Pendiente',
    };
}

function enlaceCorreoConsulta(array $consulta): string
{
    $asuntoOriginal = trim((string) $consulta['asunto']);
    $asunto = str_starts_with(mb_strtolower($asuntoOriginal), 're:') ? $asuntoOriginal : 'Re: ' . $asuntoOriginal;
    $cuerpo = "Hola " . $consulta['nombre'] . ",\r\n\r\n" . $consulta['respuesta'] . "\r\n\r\nSaludos,\r\nAtención al Cliente - Logística Delta";
    return 'mailto:' . rawurlencode((string) $consulta['email']) . '?subject=' . rawurlencode($asunto) . '&body=' . rawurlencode($cuerpo);
}
?>
<!doctype html>
<html lang="<?= limpiar(idiomaActual()) ?>">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="media/favicon.png"><meta name="theme-color" content="#073486">
  <title>Atención al cliente | Logística Delta</title><link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php $paginaActual = 'atencion-cliente'; include __DIR__ . '/partials/nav.php'; ?>
  <main class="contenedor pagina-operaciones">
    <h1>Atención al cliente</h1>
    <p class="bajada">Revisá los mensajes que llegan desde Contacto, prepará una respuesta y registrá cuándo se envió.</p>
    <div class="barra-operaciones"><p>Hola, <?= limpiar($usuario['nombre']) ?> · Atención al cliente</p></div>
    <?php if ($error || $mensaje): ?><p class="respuesta-operaciones" role="status"><?= limpiar($error ?: $mensaje) ?></p><?php endif; ?>

    <nav class="filtros-consultas" aria-label="Filtrar consultas">
      <?php foreach (['TODAS' => 'Todas', 'PENDIENTE' => 'Pendientes', 'RESPUESTA_PREPARADA' => 'Preparadas', 'RESPONDIDA' => 'Respondidas'] as $valor => $texto): ?>
        <a class="boton-secundario" href="atencion-cliente.php?estado=<?= urlencode($valor) ?>" <?= $filtro === $valor ? 'aria-current="page"' : '' ?>><?= limpiar($texto) ?></a>
      <?php endforeach; ?>
    </nav>

    <?php if (!$consultas): ?><section class="panel-operaciones"><p>No hay consultas en este filtro.</p></section><?php endif; ?>
    <div class="bandeja-consultas">
      <?php foreach ($consultas as $consulta): ?>
        <?php
          $idConsulta = (int) $consulta['id_consulta'];
          $correoListo = isset($_GET['correo']) && (int) $_GET['correo'] === $idConsulta && $consulta['estado'] === 'RESPUESTA_PREPARADA';
        ?>
        <article class="panel-operaciones tarjeta-consulta">
          <header class="encabezado-consulta">
            <div><h2><?= limpiar($consulta['asunto']) ?></h2><p><?= limpiar($consulta['nombre']) ?> · <a href="mailto:<?= limpiar($consulta['email']) ?>"><?= limpiar($consulta['email']) ?></a></p></div>
            <span class="estado-consulta estado-<?= limpiar(strtolower($consulta['estado'])) ?>"><?= limpiar(etiquetaEstadoConsulta($consulta['estado'])) ?></span>
          </header>
          <?php if (!empty($consulta['id_envio'])): ?><p><strong>Envío relacionado:</strong> #<?= limpiar($consulta['id_envio']) ?></p><?php endif; ?>
          <div class="mensaje-original"><strong>Mensaje del cliente</strong><p><?= nl2br(limpiar($consulta['mensaje'])) ?></p></div>

          <?php if ($consulta['estado'] === 'PENDIENTE'): ?>
            <form method="post" class="formulario-respuesta-consulta">
              <input type="hidden" name="accion" value="preparar"><input type="hidden" name="id_consulta" value="<?= $idConsulta ?>">
              <label for="respuesta-<?= $idConsulta ?>">Respuesta</label>
              <textarea id="respuesta-<?= $idConsulta ?>" name="respuesta" maxlength="3000" required><?= limpiar($consulta['respuesta'] ?? '') ?></textarea>
              <button class="boton-principal" type="submit">Guardar respuesta y preparar correo</button>
            </form>
          <?php elseif ($consulta['estado'] === 'RESPUESTA_PREPARADA'): ?>
            <div class="mensaje-original"><strong>Respuesta preparada</strong><p><?= nl2br(limpiar($consulta['respuesta'])) ?></p>
              <?php if (!empty($consulta['nombre_responsable'])): ?><small>Preparada por <?= limpiar($consulta['nombre_responsable'] . ' ' . $consulta['apellido_responsable']) ?></small><?php endif; ?>
            </div>
            <div class="acciones-respuesta-consulta">
              <a class="boton-principal" href="<?= limpiar(enlaceCorreoConsulta($consulta)) ?>">Abrir correo para enviar</a>
              <form method="post" onsubmit="return confirm('¿Ya enviaste el correo al cliente?');">
                <input type="hidden" name="accion" value="marcar_respondida"><input type="hidden" name="id_consulta" value="<?= $idConsulta ?>">
                <button class="boton-secundario" type="submit">Ya la envié · marcar respondida</button>
              </form>
            </div>
            <?php if ($correoListo): ?><p class="nota-consulta">Se abrió (o está listo para abrirse) un borrador de correo. Después de enviarlo, marcá la consulta como respondida.</p><?php endif; ?>
          <?php else: ?>
            <div class="mensaje-original"><strong>Respuesta enviada</strong><p><?= nl2br(limpiar($consulta['respuesta'] ?? '')) ?></p>
              <?php if (!empty($consulta['nombre_responsable'])): ?><small>Atendida por <?= limpiar($consulta['nombre_responsable'] . ' ' . $consulta['apellido_responsable']) ?></small><?php endif; ?>
            </div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </main>
  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
