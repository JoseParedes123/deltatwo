<?php
require_once __DIR__ . '/../../app/helpers/funciones.php';
iniciarSesion();
// la navegación cambia según el rol guardado en la sesión.
$usuarioNav = usuarioActual();
$paginaActual = $paginaActual ?? '';
$idiomaNav = idiomaActual();
?>
<header class="encabezado-sitio">
  <!-- la marca, los enlaces y las acciones se adaptan al rol activo. -->
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
      <?php if ($usuarioNav): ?>
        <?php
          $esClienteNav = $usuarioNav['rol'] === 'CLIENTE';
          $enlaceCuentaNav = $esClienteNav ? 'envios.php' : 'panel-admin.php';
          $textoCuentaNav = $esClienteNav ? t('MI CUENTA') : t('OPERACIONES');
        ?>
        <details class="menu-cuenta">
          <summary aria-label="<?= limpiar(t('Abrir menú de cuenta')) ?>" title="<?= limpiar(t('Abrir menú de cuenta')) ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="4"></circle><path d="M4.5 21a7.5 7.5 0 0 1 15 0"></path></svg>
            <span class="solo-lectores"><?= limpiar(t('Cuenta')) ?></span>
          </summary>
          <div class="contenido-menu-cuenta">
            <span class="nombre-menu-cuenta"><?= limpiar($usuarioNav['nombre']) ?></span>
            <a href="<?= limpiar($enlaceCuentaNav) ?>"><?= limpiar($textoCuentaNav) ?></a>
            <form method="post" action="logout.php?lang=<?= limpiar($idiomaNav) ?>">
              <button type="submit"><?= limpiar(t('CERRAR SESIÓN')) ?></button>
            </form>
          </div>
        </details>
      <?php else: ?>
        <a class="enlace-acceso" href="login.php"><?= limpiar(t('INICIAR SESIÓN')) ?></a>
        <a class="enlace-acceso" href="registro.php"><?= limpiar(t('REGISTRARSE')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</header>
