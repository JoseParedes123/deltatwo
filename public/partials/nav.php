<?php
require_once __DIR__ . '/../../app/helpers/funciones.php';
iniciarSesion();
// La navegación cambia según el rol guardado en la sesión.
$usuarioNav = usuarioActual();
$paginaActual = $paginaActual ?? '';
?>
<header class="encabezado-sitio">
  <div class="contenedor barra-navegacion">
    <a class="marca" href="index.php" aria-label="Logística Delta, inicio">
      <img src="./media/Logistica_Delta.svg" width="50" height="50" alt="">
    <span class="nombre-marca"><span>LOGÍSTICA</span><strong>DELTA</strong></span>
    </a>
    <button class="boton-menu" type="button" aria-label="Abrir navegación" aria-expanded="false">☰</button>
    <nav class="enlaces-navegacion" aria-label="Navegación principal">
      <a href="index.php" <?= $paginaActual === 'inicio' ? 'aria-current="page"' : '' ?>>INICIO</a>
      <a href="seguimiento.php" <?= $paginaActual === 'seguimiento' ? 'aria-current="page"' : '' ?>>SEGUIMIENTO</a>
      <a href="servicios.php" <?= $paginaActual === 'servicios' ? 'aria-current="page"' : '' ?>>SERVICIOS</a>
      <a href="contacto.php" <?= $paginaActual === 'contacto' ? 'aria-current="page"' : '' ?>>CONTACTO</a>
    </nav>
    <div class="cuenta-navegacion">
      <?php if (!$usuarioNav || $usuarioNav['rol'] === 'CLIENTE'): ?>
        <a class="enlace-acceso enlace-personal" href="panel-admin.php">PERSONAL</a>
      <?php endif; ?>
      <?php if ($usuarioNav && $usuarioNav['rol'] === 'CLIENTE'): ?>
        <a class="enlace-acceso" href="envios.php">MI CUENTA</a>
        <form method="post" action="logout.php"><button class="enlace-acceso" type="submit">CERRAR SESIÓN</button></form>
      <?php elseif ($usuarioNav && in_array($usuarioNav['rol'], ['ADMINISTRADOR', 'OPERADOR_DEPOSITO', 'ADMINISTRACION'], true)): ?>
        <a class="enlace-acceso" href="panel-admin.php">OPERACIONES</a>
        <form method="post" action="logout.php"><button class="enlace-acceso" type="submit">CERRAR SESIÓN</button></form>
      <?php else: ?>
        <a class="enlace-acceso" href="login.php">INICIAR SESIÓN</a>
        <a class="enlace-acceso" href="registro.php">REGISTRARSE</a>
      <?php endif; ?>
    </div>
  </div>
</header>
