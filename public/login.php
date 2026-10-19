<?php
require_once __DIR__ . '/../app/helpers/funciones.php';
require_once __DIR__ . '/../app/dao/UsuarioDAO.php';
iniciarSesion();

if (($_SESSION['rol'] ?? '') === 'CLIENTE') {
    header('Location: envios.php');
    exit;
}

$error = '';
$email = trim($_POST['email'] ?? '');
// Comprueba las credenciales mediante el DAO y conserva el acceso en la sesión.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $usuario = (new UsuarioDAO())->validarLogin($email, $_POST['password'] ?? '');
        if ($usuario && $usuario['rol'] === 'CLIENTE') {
            session_regenerate_id(true);
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];
            header('Location: envios.php');
            exit;
        }
        $error = 'No pudimos iniciar sesión. Revisá tus datos o registrate como cliente.';
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        $error = 'No pudimos iniciar sesión. Revisá que MySQL esté activo.';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#073486"><title>Iniciar sesión | Logística Delta</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include __DIR__ . '/partials/nav.php'; ?>
  <main class="disposicion-acceso">
    <section class="lateral-formulario">
      <form class="formulario-acceso" method="post">
        <h1>Iniciar sesión</h1>
        <p class="subtitulo">Ingresá tus datos para acceder a tu cuenta.</p>
        <div class="campo-entrada"><span class="icono-entrada" aria-hidden="true">＠</span><input type="text" name="email" placeholder="Email o nombre de usuario" aria-label="Email o nombre de usuario" autocomplete="username" required value="<?= limpiar($email) ?>"></div>
        <div class="campo-entrada"><span class="icono-entrada" aria-hidden="true">▣</span><input type="password" name="password" placeholder="Contraseña" aria-label="Contraseña" autocomplete="current-password" required></div>
        <?php if ($error): ?><p class="respuesta-formulario respuesta-acceso" role="alert"><?= limpiar($error) ?></p><?php endif; ?>
        <div class="opciones-acceso"><span>¿Necesitás ayuda?</span><a class="enlace-texto" href="contacto.php">Contactanos</a></div>
        <button class="boton-principal boton-acceso" type="submit">Iniciar sesión</button>
        <p class="alternativa-acceso"><a class="enlace-texto" href="registro.php">¿No tenés una cuenta? Registrate</a></p>
      </form>
    </section>
    <aside class="foto-acceso" aria-label="Camión de Logística Delta"><div class="logo-acceso"><svg viewBox="0 0 100 100" aria-hidden="true"><path d="M50 8 92 84H8L50 8Z" fill="none" stroke="white" stroke-width="8"/><path d="m22 78 48-39M41 81l36-29M57 82l20-16" fill="none" stroke="white" stroke-width="5"/></svg>LOGÍSTICA DELTA</div></aside>
  </main>
  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
