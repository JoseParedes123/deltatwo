<?php
require_once __DIR__ . '/../../app/helpers/funciones.php';
iniciarSesion();
// La navegación cambia según el rol guardado en la sesión.
$usuarioNav = usuarioActual();
$paginaActual = $paginaActual ?? '';
$idiomaNav = idiomaActual();
?>
<header class="encabezado-sitio">
  <!-- La marca, los enlaces y las acciones se adaptan al rol activo. -->
  <div class="contenedor barra-navegacion">
    <a class="marca" href="index.php" aria-label="<?= limpiar(t('Logística Delta, inicio')) ?>">
      <img src="./media/Logistica_Delta.svg" width="50" height="50" alt="">
    <span class="nombre-marca"><span><?= limpiar(t('LOGÍSTICA')) ?></span><strong>DELTA</strong></span>
    </a>
    <button class="boton-menu" type="button" aria-label="<?= limpiar(t('Abrir navegación')) ?>" data-label-open="<?= limpiar(t('Abrir navegación')) ?>" data-label-close="<?= limpiar(t('Cerrar navegación')) ?>" aria-expanded="false">☰</button>
    <nav class="enlaces-navegacion" aria-label="<?= limpiar(t('Navegación principal')) ?>">
      <a href="index.php" <?= $paginaActual === 'inicio' ? 'aria-current="page"' : '' ?>><?= limpiar(t('INICIO')) ?></a>
      <a href="seguimiento.php" <?= $paginaActual === 'seguimiento' ? 'aria-current="page"' : '' ?>><?= limpiar(t('SEGUIMIENTO')) ?></a>
      <a href="servicios.php" <?= $paginaActual === 'servicios' ? 'aria-current="page"' : '' ?>><?= limpiar(t('SERVICIOS')) ?></a>
      <a href="contacto.php" <?= $paginaActual === 'contacto' ? 'aria-current="page"' : '' ?>><?= limpiar(t('CONTACTO')) ?></a>
    </nav>
    <div class="cuenta-navegacion">
      <div class="selector-idioma" role="group" aria-label="<?= limpiar(t('Idioma')) ?>">
        <a href="<?= limpiar(urlIdioma('es')) ?>" lang="es" hreflang="es" aria-pressed="<?= $idiomaNav === 'es' ? 'true' : 'false' ?>">ES</a>
        <a href="<?= limpiar(urlIdioma('en')) ?>" lang="en" hreflang="en" aria-pressed="<?= $idiomaNav === 'en' ? 'true' : 'false' ?>">EN</a>
      </div>
      <?php if ($usuarioNav && $usuarioNav['rol'] === 'CLIENTE'): ?>
        <a class="enlace-acceso" href="envios.php"><?= limpiar(t('MI CUENTA')) ?></a>
        <form method="post" action="logout.php?lang=<?= limpiar($idiomaNav) ?>"><button class="enlace-acceso" type="submit"><?= limpiar(t('CERRAR SESIÓN')) ?></button></form>
      <?php elseif ($usuarioNav && in_array($usuarioNav['rol'], ['ADMINISTRADOR', 'OPERADOR_DEPOSITO', 'ADMINISTRACION'], true)): ?>
        <a class="enlace-acceso" href="panel-admin.php"><?= limpiar(t('OPERACIONES')) ?></a>
        <form method="post" action="logout.php?lang=<?= limpiar($idiomaNav) ?>"><button class="enlace-acceso" type="submit"><?= limpiar(t('CERRAR SESIÓN')) ?></button></form>
      <?php else: ?>
        <a class="enlace-acceso" href="login.php"><?= limpiar(t('INICIAR SESIÓN')) ?></a>
        <a class="enlace-acceso" href="registro.php"><?= limpiar(t('REGISTRARSE')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</header>
