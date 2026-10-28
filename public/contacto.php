<?php
$paginaActual = 'contacto';
require_once __DIR__ . '/../app/helpers/funciones.php';
require_once __DIR__ . '/../app/dao/EnvioDAO.php';
require_once __DIR__ . '/../app/dao/ClienteDAO.php';
require_once __DIR__ . '/../app/dao/ConsultaWebDAO.php';
iniciarSesion();

$error = '';
$mensaje = '';
$datos = ['name' => '', 'email' => '', 'codigo' => '', 'subject' => '', 'message' => ''];

// Valida la consulta y la asocia a un envío o cliente cuando corresponde.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($datos as $campo => $valor) {
        $datos[$campo] = trim($_POST[$campo] ?? '');
    }
    $email = filter_var($datos['email'], FILTER_VALIDATE_EMAIL);

    if ($datos['name'] === '' || !$email || $datos['subject'] === '' || $datos['message'] === '' || strlen($datos['message']) > 3000) {
        $error = t('Completá tu nombre, email, motivo y mensaje.');
    } else {
        try {
            $envioDAO = new EnvioDAO();
            $idEnvio = $datos['codigo'] === '' ? null : $envioDAO->obtenerIdPorCodigo(strtoupper($datos['codigo']));
            if ($datos['codigo'] !== '' && !$idEnvio) {
                $error = t('No encontramos ese código. Revisalo o dejá el campo vacío.');
            } else {
                $usuario = usuarioActual();
                $cliente = $usuario && $usuario['rol'] === 'CLIENTE'
                    ? (new ClienteDAO())->obtenerPorUsuario($usuario['id_usuario'])
                    : null;
                (new ConsultaWebDAO())->crear($cliente ? (int) $cliente['id_cliente'] : null, $idEnvio, $datos['name'], $email, $datos['subject'], $datos['message']);
                $_SESSION['mensaje'] = t('Listo, recibimos tu consulta. El equipo de Logística Delta se pondrá en contacto.');
                header('Location: contacto.php');
                exit;
            }
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $error = t('No pudimos guardar tu consulta. Intentá nuevamente más tarde.');
        }
    }
}
$mensaje = mensajeFlasheado() ?? '';
?>
<!doctype html>
<html lang="<?= limpiar(idiomaActual()) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#073486">
  <title><?= limpiar(t('Contacto')) ?> | Logística Delta</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include __DIR__ . '/partials/nav.php'; ?>
  <main>
    <div class="titulo-cinta">
      <h1><?= limpiar(t('Contacto')) ?></h1>
    </div>
    <p class="bajada-contacto"><?= limpiar(t('En Logística Delta ofrecemos soluciones completas para que tus envíos lleguen de forma segura, rápida y confiable.')) ?></p>
    <section class="panel-contacto" aria-label="<?= limpiar(t('Datos y formulario de contacto')) ?>">
      <div class="datos-contacto">
        <a class="elemento-contacto" href="mailto:logistica.delta@gmail.com">
          <span class="icono-contacto" aria-hidden="true">✉</span>
          <span><strong>Email</strong><span>logistica.delta@gmail.com</span></span>
        </a>
        <div class="elemento-contacto">
          <span class="icono-contacto" aria-hidden="true">◷</span>
          <span><strong><?= limpiar(t('Horarios')) ?></strong><span><?= limpiar(t('Lun a Vie. 8 a 18 hs.')) ?></span></span>
        </div>
        <a class="elemento-contacto" href="tel:+541145678900">
          <span class="icono-contacto" aria-hidden="true">☎</span>
          <span><strong><?= limpiar(t('Teléfono')) ?></strong><span>+54 11 4567-8900</span></span>
        </a>
      </div>
      <div class="mensaje-contacto">
        <form method="post">
          <label for="contact-name"><?= limpiar(t('Nombre')) ?></label>
          <input id="contact-name" name="name" autocomplete="name" required maxlength="120" value="<?= limpiar($datos['name']) ?>">
          <label for="contact-email">Email</label>
          <input id="contact-email" name="email" type="email" autocomplete="email" required maxlength="120" value="<?= limpiar($datos['email']) ?>">
          <label for="contact-code"><?= limpiar(t('Código de envío (opcional)')) ?></label>
          <input id="contact-code" name="codigo" placeholder="<?= limpiar(t('DEL-… o ENV-…')) ?>" maxlength="20" value="<?= limpiar($datos['codigo']) ?>">
          <label for="contact-subject"><?= limpiar(t('Motivo')) ?></label>
          <select id="contact-subject" name="subject" required>
            <option value=""><?= limpiar(t('Elegí un motivo')) ?></option>
            <?php foreach (['Estado del envío' => 'Consulta sobre mi envío', 'Problema con la entrega' => 'Problema con la entrega', 'Paquete dañado' => 'Paquete dañado', 'Otro' => 'Otro'] as $value => $label): ?>
              <option value="<?= limpiar($value) ?>" <?= $datos['subject'] === $value ? 'selected' : '' ?>><?= limpiar(t($label)) ?></option>
            <?php endforeach; ?>
          </select>
          <label for="message"><?= limpiar(t('Mensaje')) ?></label>
          <textarea id="message" name="message" aria-label="<?= limpiar(t('Escribí tu mensaje')) ?>" required maxlength="3000"><?= limpiar($datos['message']) ?></textarea>
          <button class="boton-principal" type="submit"><?= limpiar(t('Enviar consulta')) ?></button>
          <p class="respuesta-formulario" role="status" aria-live="polite"><?= limpiar($error ?: $mensaje) ?></p>
        </form>
      </div>
    </section>
  </main>
  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

