<?php
require_once __DIR__ . '/../app/helpers/funciones.php';
require_once __DIR__ . '/../app/config/Conexion.php';
require_once __DIR__ . '/../app/dao/UsuarioDAO.php';
require_once __DIR__ . '/../app/dao/ClienteDAO.php';
iniciarSesion();

if (($_SESSION['rol'] ?? '') === 'CLIENTE') {
    header('Location: envios.php');
    exit;
}

$campos = ['first-name' => '', 'last-name' => '', 'email' => '', 'username' => ''];
$error = '';
// valida el formulario y crea la cuenta junto con el perfil de cliente en una transacción.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($campos as $campo => $valor) $campos[$campo] = trim($_POST[$campo] ?? '');
    $email = filter_var($campos['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$campos['first-name'] || !$campos['last-name'] || !$email || !$campos['username'] || strlen($password) < 8) {
        $error = t('Completá todos los campos y usá una contraseña de al menos 8 caracteres.');
    } else {
        $db = null;
        try {
            $db = (new Conexion())->conectar();
            $db->beginTransaction();
            $usuarioDAO = new UsuarioDAO($db);
            $idUsuario = $usuarioDAO->registrar($campos['first-name'], $campos['last-name'], null, $email, $campos['username'], $password, 'CLIENTE');
            (new ClienteDAO($db))->crear($idUsuario, 'PARTICULAR', null, null, null, null);
            $db->commit();

            session_regenerate_id(true);
            $_SESSION['id_usuario'] = $idUsuario;
            $_SESSION['nombre'] = $campos['first-name'];
            $_SESSION['rol'] = 'CLIENTE';
            header('Location: envios.php');
            exit;
        } catch (Throwable $exception) {
            if ($db && $db->inTransaction()) $db->rollBack();
            $error = $exception->getCode() === '23000'
                ? t('Ese email o nombre de usuario ya está registrado.')
                : t('No pudimos crear la cuenta en este momento. Intentá nuevamente más tarde.');
            error_log($exception->getMessage());
        }
    }
}
?>
<!doctype html>
<html lang="<?= limpiar(idiomaActual()) ?>">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="media/favicon.png">
  <meta name="theme-color" content="#073486"><title><?= limpiar(t('Registrarse')) ?> | Logística Delta</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include __DIR__ . '/partials/nav.php'; ?>
  <main class="disposicion-acceso disposicion-registro">
    <!-- el formulario crea la cuenta y el perfil de cliente asociado. -->
    <section class="lateral-formulario">
      <form class="formulario-acceso" method="post">
        <h1><?= limpiar(t('Registrarse')) ?></h1><p class="subtitulo"><?= limpiar(t('Completá tus datos para crear una cuenta.')) ?></p>
        <?php if ($error): ?><p class="respuesta-formulario respuesta-acceso" role="alert"><?= limpiar($error) ?></p><?php endif; ?>
        <div class="campo-entrada"><label for="first-name"><?= limpiar(t('Nombre')) ?></label><span class="icono-entrada" aria-hidden="true">●</span><input id="first-name" name="first-name" placeholder="Juan" autocomplete="given-name" required value="<?= limpiar($campos['first-name']) ?>"></div>
        <div class="campo-entrada"><label for="last-name"><?= limpiar(t('Apellido')) ?></label><span class="icono-entrada" aria-hidden="true">●</span><input id="last-name" name="last-name" placeholder="Díaz" autocomplete="family-name" required value="<?= limpiar($campos['last-name']) ?>"></div>
        <div class="campo-entrada"><label for="register-email">Email</label><span class="icono-entrada" aria-hidden="true">＠</span><input id="register-email" type="email" name="email" placeholder="juan.diaz@gmail.com" autocomplete="email" required value="<?= limpiar($campos['email']) ?>"></div>
        <div class="campo-entrada"><label for="username"><?= limpiar(t('Usuario')) ?></label><span class="icono-entrada" aria-hidden="true">♙</span><input id="username" name="username" placeholder="Juanito" autocomplete="username" required value="<?= limpiar($campos['username']) ?>"></div>
        <div class="campo-entrada"><label for="register-password"><?= limpiar(t('Contraseña')) ?></label><span class="icono-entrada" aria-hidden="true">▣</span><input id="register-password" type="password" name="password" placeholder="<?= limpiar(t('Mínimo 8 caracteres')) ?>" autocomplete="new-password" minlength="8" required></div>
        <button class="boton-principal boton-acceso" type="submit"><?= limpiar(t('Registrarse')) ?></button>
        <p class="alternativa-acceso"><a class="enlace-texto" href="login.php"><?= limpiar(t('¿Ya tenés una cuenta? Iniciá sesión')) ?></a></p>
      </form>
    </section>
    <aside class="foto-acceso" aria-label="<?= limpiar(t('Camión de Logística Delta')) ?>"><div class="logo-acceso"><svg viewBox="0 0 100 100" aria-hidden="true"><path d="M50 8 92 84H8L50 8Z" fill="none" stroke="white" stroke-width="8"/><path d="m22 78 48-39M41 81l36-29M57 82l20-16" fill="none" stroke="white" stroke-width="5"/></svg><?= limpiar(t('LOGÍSTICA DELTA')) ?></div></aside>
  </main>
  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
